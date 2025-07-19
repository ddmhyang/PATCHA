<?php
// --- 파일 경로: /actions/ajax_upload_image.php (최종 수정본) ---
require_once '../includes/db.php';

// 응답 형식을 JSON으로 명시합니다.
header('Content-Type: application/json');

// 권한 확인 (adolfolil 프로젝트의 세션 변수명인 'loggedin' 사용)
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
    // 성공 시, URL을 배열에 담아 JSON 형식으로 반환합니다.
    echo json_encode([
        'success' => true, 
        'urls' => ['../uploads/' . $newFileName]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '파일 저장에 실패했습니다.']);
}
?>