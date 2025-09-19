<?php
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = time() . '_' . basename($_FILES["file"]["name"]);
    $target_file = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        // 성공 시 파일의 웹 경로를 반환
        echo 'uploads/' . $filename;
    } else {
        // 실패 시 오류 메시지 반환 (HTTP 상태 코드로)
        header("HTTP/1.1 500 Internal Server Error");
    }
} else {
    header("HTTP/1.1 400 Bad Request");
}
?>