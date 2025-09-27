<?php
session_start(); // 이 줄을 추가하세요!
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

// 1. 로그인 여부 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['error' => '권한이 없습니다. 로그인 후 이용해주세요.']);
    exit;
}

// 2. 업로드된 파일이 있는지 확인
if (empty($_FILES['file']) && empty($_FILES['thumbnail'])) {
    echo json_encode(['error' => '업로드된 파일이 없습니다.']);
    exit;
}

// 썸네일('thumbnail')과 일반 파일('file') 모두 처리
$file = $_FILES['file'] ?? $_FILES['thumbnail'];

// 3. 파일 에러 확인
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => '파일 업로드 중 오류가 발생했습니다. 코드: ' . $file['error']]);
    exit;
}

// 4. 업로드 폴더 설정 및 생성
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 5. 고유한 파일 이름 생성 및 경로 설정
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = uniqid('img_') . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

// 6. 파일을 지정된 경로로 이동
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // 7. 성공 시 이미지 URL을 JSON으로 반환
    // 웹에서 접근 가능한 절대 경로를 만들어줍니다.
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    $url = $protocol . "://" . $host . $path . '/' . $targetPath;
    
    echo json_encode(['url' => $url]);
} else {
    echo json_encode(['error' => '파일을 서버에 저장하는 데 실패했습니다.']);
}
?>