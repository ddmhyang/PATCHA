<?php
// chanlan 프로젝트의 업로드 로직을 catalys 환경에 맞게 수정

// 1. 업로드 디렉토리 경로를 현재 파일 위치 기준으로 설정
$upload_dir = __DIR__ . '/../uploads/';

// 2. 디렉토리가 없으면 생성
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// 3. 파일이 정상적으로 전송되었는지 확인
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    
    // 4. 파일명 고유화
    $original_name = basename($_FILES["file"]["name"]);
    $safe_name = preg_replace("/[^A-Za-z0-9\._-]/", '', $original_name);
    $filename = time() . '_' . $safe_name;
    $target_file = $upload_dir . $filename;
    
    // 5. 파일을 지정된 경로로 이동
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // 성공 시, 웹 루트 기준의 절대 경로를 반환해야 에디터가 이미지를 제대로 표시합니다.
        echo '/uploads/' . $filename;
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo '오류: 파일을 uploads 폴더로 옮기지 못했습니다. 폴더의 쓰기 권한(777)을 확인해주세요.';
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    $error_code = isset($_FILES['file']['error']) ? $_FILES['file']['error'] : '파일 없음';
    echo '오류: 파일 업로드에 실패했습니다. (오류 코드: ' . $error_code . ')';
}
?>