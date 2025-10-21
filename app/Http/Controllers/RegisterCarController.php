<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

use Maatwebsite\Excel\Facades\Excel;

class RegisterCarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('welcome'); 
    }

    public function showList() {
        return view('list'); 
    }

    public function getList(Request $request) {
        $perPage = 10;
        $page = $request->input('page', 1);

        // Query Builder
        $query = DB::table('vehicle_registrations')->orderBy('id', 'desc');

        // Lấy tổng số bản ghi
        $total = $query->count();

        // Lấy dữ liệu theo trang
        $cars = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data' => $cars,
            'pagination' => [
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('register'); 
    }

     public function createByExcel()
    {
        return view('register-by-excel'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            // 'register_date' => 'required|date',
            'delivery_date' => 'date|after:register_date',
            'contract_no' => 'required|string|max:100',
            'truck_plate' => 'required|string|max:50',
            'trailer_plate' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'wheel' => 'nullable|integer|min:4|max:50',
            'truck_weight' => 'nullable|numeric|min:0',
            'pay_load' => 'nullable|numeric|min:0',
            'container_no1' => 'nullable|string|max:50',
            'container_no2' => 'nullable|string|max:50',
            'driver_name' => 'required|string|max:100',
            'id_passport' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:20',
            'destination_est' => 'nullable|string|max:100',
            'transportation_company' => 'required|string|max:255',
            'subcontractor' => 'nullable|string|max:100',
            'vehicle_status' => 'nullable|string|max:50',
            'note' => 'nullable|string',
        ], [
            // 'register_date.required' => 'Vui lòng chọn ngày đăng ký',
            'delivery_date.required' => 'Vui lòng chọn ngày nhận hàng',
            'delivery_date.after' => 'Ngày nhận hàng phải sau ngày đăng ký',
            'contract_no.required' => 'Vui lòng nhập số hợp đồng',
            'truck_plate.required' => 'Vui lòng nhập biển số xe',
            'driver_name.required' => 'Vui lòng nhập tên lái xe',
            'id_passport.required' => 'Vui lòng nhập CMND/Passport',
            'transportation_company.required' => 'Vui lòng nhập đơn vị vận chuyển',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            
            // $registerDate = Carbon::parse($request->register_date);
            $registerDate = Carbon::now();
        
            $nextDay = $registerDate->addDay()->format('Y-m-d');
            // if ($registerDate < $ngayNhanHang != 1) {
            //     throw new Exception('Phải đăng ký trước 1 ngày. Ngày nhận hàng phải là ngày mai.');
            // }

            $currentTime = Carbon::now()->format('H:i:s');
            $currentTime = Carbon::createFromFormat('H:i:s', $currentTime);

            // Tạo hai khung giờ cho phép
            $startTime1 = Carbon::createFromTime(8, 0, 0);   // 08:00:00
            $endTime1 = Carbon::createFromTime(16, 0, 0); // 16:00:00

            $startTime2 = Carbon::createFromTime(20, 0, 0);  // 20:00:00
            $endTime2 = Carbon::createFromTime(23, 50, 0); // 22:00:00

            // Kiểm tra nếu KHÔNG nằm trong các khung giờ hợp lệ
        
            if (
                !($currentTime->between($startTime1, $endTime1)) &&
                !($currentTime->between($startTime2, $endTime2))
            ) {
                throw new Exception('Bạn chỉ có thể đăng ký trong khung giờ 08:00–16:00 hoặc 20:00–22:00.');
            }

            $existingCompany = DB::table('vehicle_registrations')
                ->where('truck_plate', $request->truck_plate)
                ->whereNotIn('registration_status', ['cancelled', 'rejected'])
                ->select('transportation_company', 'created_at')
                ->first();
            if ($existingCompany) {
                // Xe đã tồn tại, kiểm tra có phải cùng đơn vị không
                if ($existingCompany->transportation_company == $request->transportation_company) {
                    throw new Exception(
                        'Biển số xe ' . $request->truck_plate . ' đã được đăng ký cho đơn vị "' . 
                        $existingCompany->transportation_company . '". ' .
                        'Mỗi xe chỉ được phép chạy cho một đơn vị vận chuyển duy nhất.'
                    );
                }
            }

            // Kiểm tra xe đã đăng ký cho ngày này chưa
            $existingRegistration = DB::table('vehicle_registrations')
                ->where('truck_plate', $request->truck_plate)
                ->where('register_date', $nextDay)
                ->whereNotIn('registration_status', ['cancelled', 'rejected'])
                ->first();
            
            if ($existingRegistration) {
                throw new Exception(
                    'Xe ' . $request->truck_plate . ' đã được đăng ký cho ngày ' . 
                    Carbon::now()->format('d/m/Y') . '. Mỗi xe chỉ được đăng ký 1 lần/ngày.'
                );
            }

            // 5. Insert dữ liệu vào database
            $dangKyId = DB::table('vehicle_registrations')->insertGetId([
                'register_date' => $nextDay,
                'delivery_date' => $request->delivery_date,
                'registration_time' => '08:00',
                'registration_round' => 1,
                'contract_no' => $request->contract_no,
                'truck_plate' => $request->truck_plate,
                'trailer_plate' => $request->trailer_plate,
                'country' => $request->country ?? 'Vietnam',
                'wheel' => $request->wheel,
                'truck_weight' => $request->truck_weight,
                'pay_load' => $request->pay_load,
                'container_no1' => $request->container_no1 ? strtoupper($request->container_no1) : null,
                'container_no2' => $request->container_no2 ? strtoupper($request->container_no2) : null,
                'driver_name' => $request->driver_name,
                'id_passport' => strtoupper($request->id_passport),
                'phone_number' => $request->phone_number,
                'destination_est' => $request->destination_est,
                'transportation_company' => $request->transportation_company,
                'subcontractor' => $request->subcontractor ? strtoupper($request->subcontractor) : null,
                'vehicle_status' => $request->vehicle_status ?? 'RO',
                'registration_status' => 'pending_approval',
                'note' => $request->note,
                'created_by' => auth()->user()->name ?? 'System',
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            
            DB::commit();
            // Lấy thông tin đăng ký vừa tạo
            $registration = DB::table('vehicle_registrations')->where('id', $dangKyId)->first();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng ký xe thành công! Mã đăng ký: #' . $dangKyId,
                    'data' => $registration
                ], 200);
            }

            return redirect()->route('register-car.index')
                ->with('success', 'Đăng ký xe thành công! Mã đăng ký: #' . $dangKyId);

        } catch (Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();

            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                $message = 'Xe này đã được đăng ký trong ngày. Vui lòng kiểm tra lại.';
            } else {
                // Lấy error code từ các nguồn khác nếu errorInfo không có
                $errorCode = $e->getCode();
                if ($errorCode == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $message = 'Dữ liệu bị trùng lặp. Xe này có thể đã được đăng ký.';
                } else {
                    $message = $e->getMessage();
                }
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }

            return redirect()->back()
                ->with('error', $message)
                ->withInput();
        }
    }

    public function import(Request $request)
    {
        // Kiểm tra có file upload không
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'Vui lòng chọn file Excel.'], 400);
        }

        $file = $request->file('file');

        // Đọc dữ liệu Excel thành mảng
        $data = Excel::toArray([], $file);

        // Mảng đầu tiên trong $data là sheet đầu tiên
        $rows = $data[0];

        // Bỏ dòng tiêu đề nếu có
        unset($rows[0]);

        DB::beginTransaction();
        try {

            $insertData = [];

            $currentTime = Carbon::now()->format('H:i:s');
            $currentTime = Carbon::createFromFormat('H:i:s', $currentTime);

            // Tạo hai khung giờ cho phép
            $startTime1 = Carbon::createFromTime(8, 0, 0);   // 08:00:00
            $endTime1 = Carbon::createFromTime(16, 0, 0); // 16:00:00

            $startTime2 = Carbon::createFromTime(20, 0, 0);  // 20:00:00
            $endTime2 = Carbon::createFromTime(23, 50, 0); // 22:00:00

            // Kiểm tra nếu KHÔNG nằm trong các khung giờ hợp lệ
        
            if (
                !($currentTime->between($startTime1, $endTime1)) &&
                !($currentTime->between($startTime2, $endTime2))
            ) {
                throw new Exception('Bạn chỉ có thể đăng ký trong khung giờ 08:00–16:00 hoặc 20:00–22:00.');
            }

            $registerDate = Carbon::now();
            $nextDay = $registerDate->addDay()->format('Y-m-d');

            // Lấy tất cả xe đã đăng ký và đơn vị của chúng
            $existingTrucks = DB::table('vehicle_registrations')
                ->whereNotIn('registration_status', ['cancelled', 'rejected'])
                ->select('truck_plate', 'transportation_company', 'register_date', 'registration_status')
                ->get()
                ->groupBy('truck_plate');

            // Tạo map: truck_plate => transportation_company
            $truckCompanyMap = [];
            foreach ($existingTrucks as $truckPlate => $registrations) {
                $truckCompanyMap[$truckPlate] = $registrations->first()->transportation_company;
            }

            // Tạo map: truck_plate + date => exists
            $truckDateMap = [];
            foreach ($existingTrucks as $truckPlate => $registrations) {
                foreach ($registrations as $reg) {
                    $key = $truckPlate . '|' . $reg->register_date;
                    $truckDateMap[$key] = true;
                }
            }


            foreach ($rows as $row) {
                // Bỏ qua hàng trống
                if (empty($row[3]) || empty($row[11])) {
                    continue;
                }
                $dataTruckPlate = $row[3] ?? null;
                $transportationCompany =  $row[15] ?? null;
                // 1. Check xe đã thuộc về đơn vị khác chưa
                if (isset($truckCompanyMap[$dataTruckPlate])) {
                    $existingCompany = $truckCompanyMap[$dataTruckPlate];
                    if ($existingCompany !== $transportationCompany) {
                        throw new Exception(
                            "Xe {$dataTruckPlate} đã đăng ký cho đơn vị '{$existingCompany}'. " .
                            "Không thể đăng ký cho '{$transportationCompany}'"
                        );
                    }
                }

                // // 2. Check xe đã đăng ký cho ngày này chưa
                $checkKey = $dataTruckPlate . '|' . $nextDay;
                if (isset($truckDateMap[$checkKey])) {
                    throw new Exception(
                        "Xe {$dataTruckPlate} đã được đăng ký cho ngày {$nextDay}"
                    );
                }

                // // 3. Check trùng lặp trong file Excel (giữa các dòng)
                // foreach ($validData as $existingData) {
                //     if ($existingData['truck_plate'] === $data['truck_plate'] && 
                //         $existingData['register_date'] === $nextDay) {
                //         throw new Exception(
                //             "Xe {$dataTruckPlate} bị trùng trong file Excel (nhiều dòng cho cùng ngày)"
                //         );
                //     }
                // }

                $insertData[] = [
                    'register_date' => $nextDay,
                    'delivery_date' => $nextDay,
                    'registration_time' => '08:00',
                    'registration_round' => 1,
                    'contract_no' => $row[2] ?? null,
                    'truck_plate' => $row[3] ?? null,
                    'trailer_plate' => $row[6] ?? null,
                    'country' => $row[4] ?? null,
                    'wheel' => $row[5] ?? null,
                    'truck_weight' => $row[7] ?? null,
                    'pay_load' => $row[8] ?? null,
                    'container_no1' => $row[9] ?? null,
                    'container_no2' => $row[10] ?? null,
                    'driver_name' => $row[11] ?? null,
                    'id_passport' => $row[12] ?? null,
                    'phone_number' => $row[13] ?? null,
                    'destination_est' => $row[14] ?? null,
                    'transportation_company' => $row[15] ?? null,
                    'subcontractor' => $row[16] ?? null,
                    'vehicle_status' => $row[17] ?? null,
                    'registration_status' => 'pending_approval',
                    'note' => '',
                    'created_by' => auth()->user()->name ?? 'System',
                    'ip_address' => $request->ip(),
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ];
            }

            // Kiểm tra có dữ liệu hợp lệ không
            if (count($insertData) > 0) {
              
                // Insert một lần để tăng hiệu suất
                DB::table('vehicle_registrations')->insert($insertData);
                DB::commit();
                return response()->json([
                    'message' => 'Import dữ liệu thành công!',
                    'inserted' => count($insertData),
                ]);
            } else {
                return response()->json([
                    'message' => 'Không có dòng hợp lệ để import',
                ], 400);
            }

            

            return response()->json(['message' => 'Import dữ liệu thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                $message = 'Xe này đã được đăng ký trong ngày. Vui lòng kiểm tra lại.';
            } else {
                // Lấy error code từ các nguồn khác nếu errorInfo không có
                $errorCode = $e->getCode();
                if ($errorCode == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $message = 'Xe này đã được đăng ký trong ngày. Vui lòng kiểm tra lại.';
                } else {
                    $message = $e->getMessage();
                }
            }
            return response()->json([
                'message' => 'Lỗi khi import: ' . $message
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
