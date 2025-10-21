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
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('register'); 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            // 'register_date' => 'required|date',
            'ngay_nhan_hang' => 'required|date|after:register_date',
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
            'ghi_chu' => 'nullable|string',
        ], [
            // 'register_date.required' => 'Vui lòng chọn ngày đăng ký',
            'ngay_nhan_hang.required' => 'Vui lòng chọn ngày nhận hàng',
            'ngay_nhan_hang.after' => 'Ngày nhận hàng phải sau ngày đăng ký',
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

            // 1. Kiểm tra ngày đăng ký hợp lệ (phải đăng ký trước 1 ngày)
            // $registerDate = Carbon::parse($request->register_date);
            $registerDate = Carbon::now();
            $ngayNhanHang = Carbon::parse($request->ngay_nhan_hang);

            if ($registerDate < $ngayNhanHang != 1) {
                throw new Exception('Phải đăng ký trước 1 ngày. Ngày nhận hàng phải là ngày mai.');
            }

            $gioHienTai = Carbon::now()->format('H:i:s');

            $gioHienTai = Carbon::createFromFormat('H:i:s', $gioHienTai);

            // Tạo hai khung giờ cho phép
            $khung1_batdau = Carbon::createFromTime(8, 0, 0);   // 08:00:00
            $khung1_ketthuc = Carbon::createFromTime(16, 0, 0); // 16:00:00

            $khung2_batdau = Carbon::createFromTime(20, 0, 0);  // 20:00:00
            $khung2_ketthuc = Carbon::createFromTime(23, 50, 0); // 22:00:00

            // Kiểm tra nếu KHÔNG nằm trong các khung giờ hợp lệ
            var_dump($gioHienTai.'--'.$khung2_ketthuc);
            if (
                !($gioHienTai->between($khung1_batdau, $khung1_ketthuc)) &&
                !($gioHienTai->between($khung2_batdau, $khung2_ketthuc))
            ) {
                throw new Exception('Bạn chỉ có thể đăng ký trong khung giờ 08:00–16:00 hoặc 20:00–22:00.');
            }

            // // 2. Kiểm tra thời gian đăng ký (08:00-16:00 hoặc 20:00-22:00)
            // $gioHienTai = Carbon::now()->format('H:i:s');
            // $result = DB::select('CALL sp_check_registration_time(?, @valid, @session, @message)', [$gioHienTai]);
            // $validationResult = DB::select('SELECT @valid as valid, @session as session, @message as message')[0];
            
            // if (!$validationResult->valid) {
            //     throw new Exception($validationResult->message);
            // }

            // // 3. Chuẩn hóa biển số xe
            // $truckPlate = $this->normalizePlate($request->truck_plate);
            // $trailerPlate = $request->trailer_plate ? $this->normalizePlate($request->trailer_plate) : null;

            // // 4. Kiểm tra xe đã đăng ký chưa
            // $result = DB::select('CALL sp_check_truck_registered(?, ?, @exists, @message)', 
            //     [$truckPlate, $ngayNhanHang->format('Y-m-d')]);
            // $checkResult = DB::select('SELECT @exists as exists, @message as message')[0];
            
            // if ($checkResult->exists) {
            //     throw new Exception($checkResult->message);
            // }

            // 5. Insert dữ liệu vào database
            $dangKyId = DB::table('dang_ky_xe')->insertGetId([
                'register_date' => $registerDate->format('Y-m-d'),
                'ngay_nhan_hang' => $ngayNhanHang->format('Y-m-d'),
                'gio_dang_ky' => '',
                'lan_dang_ky' => 1,
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
                'registration_status' => 'cho_duyet',
                'ghi_chu' => $request->ghi_chu,
                'created_by' => auth()->user()->name ?? 'System',
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();

            // Lấy thông tin đăng ký vừa tạo
            $registration = DB::table('dang_ky_xe')->where('id', $dangKyId)->first();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đăng ký xe thành công! Mã đăng ký: #' . $dangKyId,
                    'data' => $registration
                ], 201);
            }

            return redirect()->route('dang-ky-xe.index')
                ->with('success', 'Đăng ký xe thành công! Mã đăng ký: #' . $dangKyId);

        } catch (Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
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

            foreach ($rows as $row) {
                // Bỏ qua hàng trống
                if (empty($row[3]) || empty($row[11])) {
                    continue;
                }

                $insertData[] = [
                    'register_date' => '2025-10-21',
                    'ngay_nhan_hang' => '2025-10-21',
                    'gio_dang_ky' => '',
                    'lan_dang_ky' => 1,
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
                    'registration_status' => 'cho_duyet',
                    'ghi_chu' => '',
                    'created_by' => auth()->user()->name ?? 'System',
                    'ip_address' => $request->ip(),
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ];
            }

            // Kiểm tra có dữ liệu hợp lệ không
            if (count($insertData) > 0) {
              
                // Insert một lần để tăng hiệu suất
                DB::table('dang_ky_xe')->insert($insertData);
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
            return response()->json([
                'message' => 'Lỗi khi import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if truck already registered (AJAX)
     */
    public function checkTruck(Request $request)
    {
        try {
            $truckPlate = $this->normalizePlate($request->truck_plate);
            $ngayNhanHang = $request->ngay_nhan_hang;

            $result = DB::select('CALL sp_check_truck_registered(?, ?, @exists, @message)', 
                [$truckPlate, $ngayNhanHang]);
            $checkResult = DB::select('SELECT @exists as exists, @message as message')[0];

            return response()->json([
                'success' => true,
                'exists' => (bool)$checkResult->exists,
                'message' => $checkResult->message
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kiểm tra: ' . $e->getMessage()
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
