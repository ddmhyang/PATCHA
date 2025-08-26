<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

// 관리자 권한 확인
if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if (empty($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => '업로드된 파일이 없습니다.']);
    exit;
}

$file = $_FILES['file'];
$uploadDir = 'uploads/'; // 파일을 저장할 디렉토리

// uploads 폴더가 없으면 생성
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 파일명 중복을 피하기 위해 고유한 이름 생성
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = 'img-' . uniqid() . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

// 파일을 지정된 경로로 이동
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Summernote 에디터에서 사용할 수 있는 웹 경로를 반환
    echo json_encode(['success' => true, 'url' => $targetPath]);
} else {
    echo json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다. 폴더 권한을 확인해주세요.']);
}
?>