<?php
// /blz/ajax_upload_thumbnail.php (신규 파일)
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}
if (empty($_FILES['thumbnail_file'])) {
    echo json_encode(['success' => false, 'message' => '업로드된 파일이 없습니다.']);
    exit;
}

$file = $_FILES['thumbnail_file'];
$uploadDir = 'uploads/thumbnails/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = uniqid('thumb-') . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => true, 'url' => $targetPath]);
} else {
    echo json_encode(['success' => false, 'message' => '썸네일 저장에 실패했습니다.']);
}
?>