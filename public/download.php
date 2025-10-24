<?php
// Lấy tên file từ tham số URL
$fileName = isset($_GET['file']) ? basename($_GET['file']) : '';
$directory = '../storage/app/public/imports/errors/';
$filePath = $directory . $fileName;

// Kiểm tra file có tồn tại và là file .xlsx
if ($fileName && file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'xlsx') {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
} else {
    http_response_code(404);
    echo 'File không tồn tại hoặc không hợp lệ!';
}
?>