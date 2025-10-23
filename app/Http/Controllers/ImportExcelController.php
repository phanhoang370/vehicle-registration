<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportExcelController extends Controller
{
    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('import');
    }

    public function import(Request $request)
    {
        // Kiểm tra có file upload không
        if (!$request->hasFile('excel_file')) {
            return response()->json(['message' => 'Vui lòng chọn file Excel.'], 400);
        }

        $file = $request->file('excel_file');

        // Đọc dữ liệu Excel thành mảng
        $data = Excel::toArray([], $file);
        // Mảng đầu tiên trong $data là sheet đầu tiên
        $rowsGet = $data[0];

        // Lấy header (dòng đầu tiên)
        $header =$rowsGet[0];
        
        // Bỏ dòng tiêu đề
        unset($rowsGet[0]);
        $rows = [];
        foreach ($rowsGet as $index => $row) {
            if ($this->isEmptyRow($row)) {
                continue; // Bỏ qua dòng trống
            }else {
                 array_push($rows, $row); 
            }
        }

        DB::beginTransaction();
        try {
            $insertData = [];
            $errorRows = []; // Mảng chứa các dòng lỗi
            $errorCount = 0;
            $successCount = 0;

            // === KIỂM TRA THỜI GIAN ===
            $currentTime = Carbon::now()->format('H:i:s');
            $currentTime = Carbon::createFromFormat('H:i:s', $currentTime);

            $startTime1 = Carbon::createFromTime(8, 0, 0);   // 08:00:00
            $endTime1 = Carbon::createFromTime(16, 0, 0);    // 16:00:00
            $startTime2 = Carbon::createFromTime(20, 0, 0);  // 20:00:00
            $endTime2 = Carbon::createFromTime(22, 00, 0);    // 22:00:00

            if (
                !($currentTime->between($startTime1, $endTime1)) &&
                !($currentTime->between($startTime2, $endTime2))
            ) {
                throw new Exception('Bạn chỉ có thể đăng ký trong khung giờ 08:00–16:00 hoặc 20:00–22:00.');
            }

            $registerDate = Carbon::now();
            $nextDay = $registerDate->copy()->addDay()->format('Y-m-d');

            // === LẤY DỮ LIỆU TỪ DATABASE ===
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

            // === XỬ LÝ TỪNG DÒNG ===
            $rowNumber = 2; // Bắt đầu từ dòng 2 (sau header)

            // Giả sử $rows là toàn bộ dữ liệu Excel (mảng các dòng)
            $plateCount = [];

            // B1️⃣: Đếm số lần xuất hiện của từng truck_plate
            foreach ($rows as $row) {
                $truckPlate = trim(strtoupper($row[3] ?? ''));
                if ($truckPlate !== '') {
                    if (!isset($plateCount[$truckPlate])) {
                        $plateCount[$truckPlate] = 0;
                    }
                    $plateCount[$truckPlate]++;
                }
            }
            foreach ($rows as $index => $row) {
                try {
                    if ($this->isEmptyRow($row)) {
                        continue; // Bỏ qua dòng trống
                    }
                    // Validate dữ liệu cơ bản
                    if (empty($row[3]) || empty($row[11]) || empty($row[15]) || empty($row[2])) {
                        throw new Exception('Thiếu thông tin bắt buộc: Biển số xe, Tên lái xe, Đơn vị vận chuyển hoặc Số hợp đồng');
                    }

                    $truckPlate = $this->normalizePlate($row[3]);
                    $transportationCompany = trim($row[15]);

                    // === CHECK TRÙNG LẶP ===
                    if (isset($plateCount[$truckPlate]) && $plateCount[$truckPlate] > 1) {
                        throw new Exception("Xe {$truckPlate} bị trùng lặp trong file Excel (xuất hiện nhiều hơn 1 lần).");
                    }
                    // 1. Check xe đã thuộc về đơn vị khác chưa
                    if (isset($truckCompanyMap[$truckPlate])) {
                        $existingCompany = $truckCompanyMap[$truckPlate];
                        if ($existingCompany !== $transportationCompany) {
                            throw new Exception(
                                "Xe {$truckPlate} đã đăng ký cho đơn vị '{$existingCompany}'. " .
                                "Không thể đăng ký cho '{$transportationCompany}'"
                            );
                        }
                    }

                    // 2. Check xe đã đăng ký cho ngày này chưa (trong DB)
                    $checkKey = $truckPlate . '|' . $nextDay;
                    if (isset($truckDateMap[$checkKey])) {
                        throw new Exception(
                            "Xe {$truckPlate} đã được đăng ký cho ngày " . Carbon::parse($nextDay)->format('d/m/Y')
                        );
                    }

                    // 3. Check trùng lặp trong file Excel (giữa các dòng)
                    foreach ($insertData as $existingData) {
                        if ($existingData['truck_plate'] === $truckPlate && 
                            $existingData['register_date'] === $nextDay) {
                            throw new Exception(
                                "Xe {$truckPlate} bị trùng trong file Excel (có nhiều dòng cùng xe cho cùng ngày)"
                            );
                        }
                    }

                    // Dữ liệu hợp lệ - Chuẩn bị insert
                    $validData = [
                        'register_date' => $nextDay,
                        'delivery_date' => $nextDay,
                        'registration_time' => $currentTime->format('H:i'),
                        'registration_round' => $currentTime->between($startTime1, $endTime1) ? 1 : 2,
                        'contract_no' => $row[2] ?? null,
                        'truck_plate' => $truckPlate,
                        'trailer_plate' => $this->normalizePlate($row[6] ?? ''),
                        'country' => $row[4] ?? 'Vietnam',
                        'wheel' => $row[5] ?? null,
                        'truck_weight' => $row[7] ?? null,
                        'pay_load' => $row[8] ?? null,
                        'container_no1' => strtoupper(trim($row[9] ?? '')),
                        'container_no2' => strtoupper(trim($row[10] ?? '')),
                        'driver_name' => $row[11] ?? null,
                        'id_passport' => strtoupper(trim($row[12] ?? '')),
                        'phone_number' => $row[13] ?? null,
                        'destination_est' => $row[14] ?? null,
                        'transportation_company' => $transportationCompany,
                        'subcontractor' => strtoupper(trim($row[16] ?? '')),
                        'vehicle_status' => $row[17] ?? 'RO',
                        'registration_status' => 'pending_approval',
                        'note' => 'Imported from Excel',
                        'created_by' => auth()->user()->name ?? 'System',
                        'ip_address' => $request->ip(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    $insertData[] = $validData;

                    // Cập nhật map để check các dòng tiếp theo
                    $truckCompanyMap[$truckPlate] = $transportationCompany;
                    $truckDateMap[$checkKey] = true;

                    $successCount++;

                } catch (Exception $e) {

                    $excelDate = $row[1] ?? null;
                    if (is_numeric($excelDate)) {
                        // Chuyển đổi từ số sang dạng "Y-m-d"
                        $registerDate = Date::excelToDateTimeObject($excelDate)->format('Y-m-d');
                    } else {
                        // Nếu Excel lưu dạng text, vẫn dùng bình thường
                        $registerDate = date('Y-m-d', strtotime($excelDate));
                    }
                    $row[1] = $registerDate ;
                    // Lưu dòng lỗi
                    $errorRows[] = [
                        'row_number' => $rowNumber,
                        'data' => $row,
                        'error' => $e->getMessage()
                    ];
                    $errorCount++;
                }

                $rowNumber++;
            }

            // === INSERT DỮ LIỆU HỢP LỆ ===
            if (count($insertData) > 0) {
                // Insert theo batch để tránh quá tải
                $chunks = array_chunk($insertData, 100);
                foreach ($chunks as $chunk) {
                    DB::table('vehicle_registrations')->insert($chunk);
                }
            }

            DB::commit();

            // === TẠO FILE EXCEL CHỨA LỖI (NẾU CÓ) ===
            $errorFilePath = null;
            if (count($errorRows) > 0) {
                $errorFilePath = $this->generateErrorExcel($errorRows, $header);
            }

            // === RESPONSE ===
            return response()->json([
                'success' => true,
                'message' => "Import thành công: {$successCount}/" . count($rows) . " dòng",
                'data' => [
                    'total' => count($rows),
                    'success' => $successCount,
                    'errors' => $errorCount,
                ],
                'error_file' => $errorFilePath ? asset('storage/' . $errorFilePath) : null,
                'error_details' => $errorRows
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            // Xử lý lỗi duplicate entry
            $message = $e->getMessage();
            
            if ($e instanceof \Illuminate\Database\QueryException) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                    $message = 'Xe này đã được đăng ký trong ngày. Vui lòng kiểm tra lại.';
                } else {
                    $errorCode = $e->getCode();
                    if ($errorCode == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $message = 'Dữ liệu bị trùng lặp. Xe này có thể đã được đăng ký.';
                    }
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import: ' . $message
            ], 500);
        }
    }

/**
 * Normalize truck plate (chuẩn hóa biển số xe)
 */
    private function normalizePlate($plate)
    {
        if (empty($plate)) {
            return '';
        }
        return strtoupper(trim(preg_replace('/\s+/', '', $plate)));
    }

/**
 * Generate Excel file for error rows
 */
    private function generateErrorExcel($errorRows, $header)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Thêm cột "LỖI" vào header
            $errorHeader = array_merge($header, ['LỖI']);
            $sheet->fromArray($errorHeader, null, 'A1');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC3545'] // Màu đỏ
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ];
            
            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($errorHeader));
            $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);

            // Thêm dữ liệu lỗi
            $rowIndex = 2;

            foreach ($errorRows as $error) {
                $rowData = array_merge(
                    $error['data'],         // Dữ liệu gốc
                    [$error['error']]       // Lỗi
                );
                $sheet->fromArray($rowData, null, 'A' . $rowIndex);
                
                // Highlight dòng lỗi với màu đỏ nhạt
                $sheet->getStyle('A' . $rowIndex . ':' . $lastColumn . $rowIndex)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFE6E6');
                
                // Style cột lỗi màu đỏ đậm
                $errorColumn = $lastColumn;
                $sheet->getStyle($errorColumn . $rowIndex)
                    ->getFont()
                    ->getColor()
                    ->setRGB('DC3545');
                $sheet->getStyle($errorColumn . $rowIndex)
                    ->getFont()
                    ->setBold(true);
                
                // Wrap text cho cột lỗi
                $sheet->getStyle($errorColumn . $rowIndex)
                    ->getAlignment()
                    ->setWrapText(true);
                
                $rowIndex++;
            }

            // Auto width cho tất cả cột
            foreach (range('A', chr(ord('A') + count($header) - 1)) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Set width cho cột STT và cột lỗi
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension($lastColumn)->setWidth(60);

            // Freeze header row
            $sheet->freezePane('A2');

            // Tạo tên file
            $fileName = 'import_errors_' . date('Ymd_His') . '.xlsx';
            $filePath = 'imports/errors/' . $fileName;
            $fullPath = storage_path('app/public/' . $filePath);

            // Tạo thư mục nếu chưa có
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Lưu file
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($fullPath);

            return $filePath;

        } catch (Exception $e) {
            \Log::error('Lỗi tạo file Excel lỗi: ' . $e->getMessage());
            return null;
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'ID',
            'Register Date',
            'Contract No',
            'Truck Plate',
            'Country',
            'Wheel',
            'Trailer Plate',
            'Truck Weight',
            'Pay Load',
            'Container No1',
            'Container No2',
            'Driver Name',
            'ID/Passport',
            'Phone Number',
            'Destination EST',
            'Transportation Company',
            'Subcontractor',
            'Vehicle Status',
            'Registration Status',
            'Time'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Add sample data
        $sampleData = [
            [
                '',
                date('m/d/Y'),
                '107Coal/NT-XPPL/2025',
                '20C-102.61',
                'Vietnam',
                '22',
                '20R-016.84',
                '21',
                '',
                '',
                '',
                'Nguyen Van A',
                'P01714015',
                '0325955043',
                'ango',
                'Nam Tien',
                'NT',
                'RO',
                '',
                ''
            ]
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']]
        ];
        $sheet->getStyle('A1:R1')->applyFromArray($headerStyle);

        // Auto width
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Download
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'template_dang_ky_xe_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    private function isEmptyRow($row)
    {
        foreach ($row as $cell) {
            if (!empty($cell)) {
                return false;
            }
        }
        return true;
    }
}