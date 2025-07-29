<?php

require_once '../includes/db.php';


header('Content-Type: application/json');


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}


if (empty($_FILES['file'])) {
    http_response_code(400); 
    echo json_encode(['success' => false, 'error' => '파일 없음: 서버로 전송된 파일이 없습니다.']);
    exit;
}

$file = $_FILES['file'];


if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500); 
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => '서버 설정(php.ini)보다 큰 파일입니다.',
        UPLOAD_ERR_FORM_SIZE  => 'HTML 폼에서 지정한 크기보다 큰 파일입니다.',
        UPLOAD_ERR_PARTIAL    => '파일이 부분적으로만 업로드되었습니다.',
        UPLOAD_ERR_NO_FILE    => '파일이 업로드되지 않았습니다.',
        UPLOAD_ERR_NO_TMP_DIR => '임시 폴더가 없습니다.',
        UPLOAD_ERR_CANT_WRITE => '디스크에 파일을 쓸 수 없습니다.',
        UPLOAD_ERR_EXTENSION  => 'PHP 확장 기능에 의해 파일 업로드가 중단되었습니다.',
    ];
    $error_message = $upload_errors[$file['error']] ?? '알 수 없는 업로드 오류';
    echo json_encode(['success' => false, 'error' => '업로드 오류: ' . $error_message]);
    exit;
}



$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    
    if (!mkdir($uploadDir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => '폴더 생성 실패: ' . $uploadDir . ' 폴더를 만들 수 없습니다. 상위 폴더의 권한을 확인해주세요.']);
        exit;
    }
}


if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '권한 오류: ' . $uploadDir . ' 폴더에 파일을 쓸 권한이 없습니다.']);
    exit;
}


$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = uniqid('img-') . '.' . $ext;
$targetPath = $uploadDir . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    
    echo json_encode([
        'success' => true, 
        'urls' => ['../uploads/' . $newFileName]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '파일 저장 실패: 임시 파일을 최종 목적지로 옮기는 데 실패했습니다.']);
}
?>