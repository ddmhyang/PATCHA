<?php
// ajax_upload_image.php
require_once 'includes/db.php';

header('Content-Type: application/json');
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'error' => '권한이 없습니다.']);
    exit;
}
if (empty($_FILES['file'])) {
    echo json_encode(['success' => false, 'error' => '업로드된 파일이 없습니다.']);
    exit;
}

$file = $_FILES['file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext === 'pdf') {
    if (!class_exists('Imagick')) {
        echo json_encode(['success' => false, 'error' => 'PDF 변환을 위한 ImageMagick이 서버에 설치되지 않았습니다.']);
        exit;
    }
    
    $uploadDir = 'uploads/pdf_images/';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

    $baseFileName = uniqid('pdf-img-') . '-' . pathinfo($file['name'], PATHINFO_FILENAME);
    
    try {
        $imagick = new Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($file['tmp_name']);
        
        $image_urls = [];
        foreach ($imagick as $i => $page) {
            $page->setImageFormat('jpeg');
            $page->setImageCompressionQuality(90);
            $imageFileName = $baseFileName . '-' . ($i + 1) . '.jpeg';
            $imagePath = $uploadDir . $imageFileName;
            $page->writeImage($imagePath);
            $image_urls[] = $imagePath;
        }
        $imagick->clear();
        $imagick->destroy();
        
        echo json_encode(['success' => true, 'urls' => $image_urls]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'PDF 변환 오류: ' . $e->getMessage()]);
    }
    
} else {
    $uploadDir = 'uploads/gallery/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $newFileName = uniqid('img-') . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['success' => true, 'urls' => [$targetPath]]);
    } else {
        echo json_encode(['success' => false, 'error' => '이미지 파일 저장에 실패했습니다.']);
    }
}
?>