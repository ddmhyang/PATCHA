<?php

$upload_dir = __DIR__ . '/../uploads/';

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    
    $original_name = basename($_FILES["file"]["name"]);
    $safe_name = preg_replace("/[^A-Za-z0-9\._-]/", '', $original_name);
    $filename = time() . '_' . $safe_name;
    $target_file = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
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