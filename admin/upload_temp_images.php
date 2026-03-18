<?php
require_once __DIR__ . '/../includes/functions.php';
admin_require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Chỉ hỗ trợ phương thức POST.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$uploaded = handle_multiple_image_uploads($_FILES['gallery_files'] ?? null, [
    'destination' => 'uploads/tmp',
    // Ảnh ở luồng này thường đã được nén trước ngay trên trình duyệt,
    // nên chỉ tối ưu lại khi file còn quá lớn để tránh nén 2 lần gây chậm.
    'optimize' => true,
    'optimize_after_bytes' => 900 * 1024,
    'max_width' => 1280,
    'jpeg_quality' => 80,
    'webp_quality' => 78,
    'max_file_size' => 12 * 1024 * 1024,
]);

if (empty($uploaded)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Không có ảnh hợp lệ được tải lên. Chỉ hỗ trợ JPG, PNG, WEBP và tối đa 12MB/ảnh.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$files = [];
foreach ($uploaded as $path) {
    $absolutePath = relative_upload_path_to_absolute($path);
    $files[] = [
        'path' => $path,
        'name' => basename($path),
        'size' => $absolutePath && is_file($absolutePath) ? (int)filesize($absolutePath) : 0,
    ];
}

echo json_encode([
    'success' => true,
    'files' => $files,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
