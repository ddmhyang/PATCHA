<?php
// /pages/ajax_upload_image.php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo "권한 없음";
    exit;
}

if (empty($_FILES['file'])) {
    http_response_code(400);
    echo "파일 없음";
    exit;
}

$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$file = $_FILES['file'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = md5(time() . $file['name']) . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    echo '../uploads/' . $newFileName;
} else {
    http_response_code(500);
    echo "업로드 실패";
}
?>