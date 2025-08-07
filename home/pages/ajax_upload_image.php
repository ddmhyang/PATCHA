<?php
require_once '../includes/db.php';

header('Content-Type: application/json');
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
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
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));


$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$newFileName = uniqid('img-') . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        'success' => true,
        'urls' => ['../uploads/' . $newFileName]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '이미지 파일 저장에 실패했습니다.']);
}
?>