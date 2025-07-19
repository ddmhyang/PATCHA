<?php
// /pages/ajax_upload_image.php (고화질/다중 페이지 최종 완성 버전)
require_once '../includes/db.php';

// --- 1. 사전 준비 및 보안 확인 ---
header('Content-Type: application/json');
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
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
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// --- 2. PDF 파일 처리 로직 ---
if ($ext === 'pdf') {
    if (!class_exists('Imagick')) {
        echo json_encode(['success' => false, 'error' => '서버에 PDF 변환 기능(ImageMagick)이 설치되지 않았습니다.']);
        exit;
    }
    
    $uploadDir = '../uploads/pdf_images/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $baseFileName = uniqid('pdf-hq-') . '-' . pathinfo($file['name'], PATHINFO_FILENAME);
    
    try {
        $imagick = new Imagick();
        // 고해상도 설정을 readImage 앞에 두는 것이 더 안정적입니다.
        $imagick->setResolution(600, 600);
        $imagick->readImage($file['tmp_name']);
        
        $num_pages = $imagick->getNumberImages();
        if ($num_pages === 0) {
            throw new Exception('PDF 파일에서 페이지를 인식할 수 없습니다.');
        }

        $image_urls = [];
        for ($i = 0; $i < $num_pages; $i++) {
            $imagick->setIteratorIndex($i);
            // 각 페이지를 새로운 Imagick 객체로 복제하여 안전하게 처리
            $page = $imagick->clone();
            $page->setImageFormat('jpeg'); // jpg보다 jpeg가 정식 명칭
            $page->setImageCompressionQuality(95);
            $page->stripImage(); 
            
            $imageFileName = $baseFileName . '-' . ($i + 1) . '.jpeg';
            $imagePath = $uploadDir . $imageFileName;
            
            if ($page->writeImage($imagePath)) {
                $image_urls[] = '../uploads/pdf_images/' . $imageFileName;
            }
            $page->clear();
        }
        
        $imagick->clear();
        $imagick->destroy();
        
        if (empty($image_urls)) {
             throw new Exception('PDF에서 이미지를 생성하지 못했습니다.');
        }

        echo json_encode(['success' => true, 'urls' => $image_urls]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'PDF 변환 중 오류 발생: ' . $e->getMessage()]);
    }
    
// --- 3. 일반 이미지 파일 처리 로직 ---
} else {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $newFileName = uniqid('img-') . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // 성공 시, 이미지 URL을 배열에 담아 JSON으로 반환 (형식 통일)
        echo json_encode([
            'success' => true, 
            'urls' => ['../uploads/' . $newFileName]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => '이미지 파일 저장에 실패했습니다.']);
    }
}
?>