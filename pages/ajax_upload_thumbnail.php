<?php
// /pages/ajax_upload_thumbnail.php
require_once '../includes/db.php'; 

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['thumbnail_file'])) {
    $file = $_FILES['thumbnail_file'];
    $uploadDir = '../uploads/thumbnails/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = '파일 업로드 오류: ' . $file['error'];
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message = '업로드된 파일이 너무 큽니다.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = '파일이 부분적으로만 업로드되었습니다.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = '업로드된 파일이 없습니다.';
                break;
            default:
                $message = '알 수 없는 파일 업로드 오류가 발생했습니다.';
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    if ($file['size'] > $maxFileSize) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '파일 크기가 너무 큽니다. (최대 5MB)']);
        exit;
    }

    if (!in_array($file['type'], $allowedTypes)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다. (JPG, PNG, GIF만 가능)']);
        exit;
    }

    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('thumb_', true) . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'url' => $fileUrl]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']);
}
?>