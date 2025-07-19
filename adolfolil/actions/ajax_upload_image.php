<?php
// --- 파일 경로: /actions/ajax_upload_image.php ---
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => '권한이 없습니다.']);
    exit;
}
if (empty($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => '업로드된 파일이 없습니다.']);
    exit;
}

$file = $_FILES['file'];
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = uniqid('img-') . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Summernote에 돌려줄 이미지 경로 (중요: 상대 경로)
    $url = '../uploads/' . $newFileName;
    echo json_encode(['success' => true, 'urls' => [$url]]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '파일 저장에 실패했습니다.']);
}
?>