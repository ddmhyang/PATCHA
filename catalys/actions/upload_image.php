<?php
header('Content-Type: text/plain; charset=utf-8');

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            header("HTTP/1.1 500 Internal Server Error");
            echo '업로드 디렉토리를 생성할 수 없습니다.';
            exit;
        }
    }
    
    // 파일명에서 위험한 문자 제거 및 고유화
    $original_name = basename($_FILES["file"]["name"]);
    $safe_name = preg_replace("/[^A-Za-z0-9\._-]/", '', $original_name);
    $filename = time() . '_' . $safe_name;
    $target_file = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // 성공 시 웹 경로를 반환
        echo 'uploads/' . $filename;
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo '파일 업로드에 실패했습니다. 서버 권한을 확인해주세요.';
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    $error_message = '파일이 없거나 업로드 중 오류가 발생했습니다. 오류 코드: ' . $_FILES['file']['error'];
    echo $error_message;
}
?>