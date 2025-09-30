<?php
// ---- 재사용할 이미지 업로드 PHP 코드 ----

header('Content-Type: application/json');
require_once '../includes/db.php'; // 관리자 체크 등을 위해 포함

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '업로드 권한이 없습니다.']);
    exit;
}

$uploadDir = '../uploads/'; // 이미지를 저장할 폴더

// 폴더가 없으면 생성
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 전송된 파일이 있고, 오류가 없는지 확인
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    
    // 파일명 중복 방지를 위해 유니크한 이름 생성
    $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $fileName;

    // 임시 파일을 실제 저장 폴더로 이동
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        // 웹에서 접근 가능한 최종 URL 생성
        $url = 'uploads/' . $fileName;
        // 성공 응답 전송
        echo json_encode(['success' => true, 'url' => $url]);
    } else {
        echo json_encode(['success' => false, 'message' => '파일 저장 실패.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '파일 업로드 오류.']);
}
?>