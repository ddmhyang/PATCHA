<?php
// /pages/pdf_to_images.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['pdf_file'])) {
    die("잘못된 접근입니다.");
}

// ImageMagick 확장이 있는지 확인
if (!class_exists('Imagick')) {
    die("서버에 ImageMagick PHP 확장이 설치되어 있지 않아 이미지 변환을 할 수 없습니다.");
}

$file = $_FILES['pdf_file'];

// 오류 확인
if ($file['error'] !== UPLOAD_ERR_OK) {
    die("파일 업로드 중 오류가 발생했습니다. 오류 코드: " . $file['error']);
}

// PDF 파일인지 확인
if ($file['type'] !== 'application/pdf') {
    die("PDF 파일만 업로드할 수 있습니다.");
}

$uploadDir = '../uploads/pdf_images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 파일명 고유하게 생성
$baseFileName = uniqid('pdf-') . '-' . pathinfo($file['name'], PATHINFO_FILENAME);
$pdfPath = $uploadDir . $baseFileName . '.pdf';

if (!move_uploaded_file($file['tmp_name'], $pdfPath)) {
    die("파일 저장에 실패했습니다. 폴더 권한을 확인하세요.");
}

$generated_images = [];
$html_code = "";

try {
    $imagick = new Imagick();
    $imagick->readImage($pdfPath);

    // PDF의 모든 페이지를 반복하여 처리
    foreach ($imagick as $index => $page) {
        $page->setImageFormat('jpg');
        $page->setImageCompressionQuality(90); // 이미지 품질 (90%)
        
        $imageFileName = $baseFileName . '-' . ($index + 1) . '.jpg';
        $imagePath = $uploadDir . $imageFileName;
        
        $page->writeImage($imagePath);
        
        // 웹에서 접근 가능한 경로
        $webPath = '../uploads/pdf_images/' . $imageFileName;
        $generated_images[] = $webPath;
        $html_code .= '<p><img src="' . $webPath . '" style="max-width:100%;"></p>' . "\n";
    }

    $imagick->clear();
    $imagick->destroy();
    
    // 원본 PDF 파일 삭제 (선택 사항)
    // unlink($pdfPath);

} catch (Exception $e) {
    die("PDF를 이미지로 변환하는 중 오류가 발생했습니다: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>변환 완료</title>
    <style>
        body { font-family: sans-serif; background-color: #333; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; background-color: #444; padding: 30px; border-radius: 8px; }
        h1 { border-bottom: 1px solid #666; padding-bottom: 15px; }
        textarea { width: 100%; height: 150px; background-color: #222; color: #eee; border: 1px solid #666; padding: 10px; box-sizing: border-box; }
        img { max-width: 100%; border: 1px solid #ccc; margin-top: 15px; }
        .info { padding: 15px; background: #556; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ 변환 완료!</h1>
        <div class="info">
            <p>아래 HTML 코드를 **전체 복사**하여 Summernote 에디터의 **&lt;/&gt; 코드 보기** 모드에 붙여넣으세요.</p>
            <p>또는, 아래 이미지들을 직접 복사(Ctrl+C)하여 에디터에 붙여넣어도 됩니다.</p>
        </div>
        
        <h2>HTML 코드</h2>
        <textarea readonly onclick="this.select();"><?php echo htmlspecialchars($html_code); ?></textarea>

        <h2>변환된 이미지 미리보기 (총 <?php echo count($generated_images); ?> 페이지)</h2>
        <?php foreach ($generated_images as $img_src): ?>
            <img src="<?php echo $img_src; ?>">
        <?php endforeach; ?>
    </div>
</body>
</html>