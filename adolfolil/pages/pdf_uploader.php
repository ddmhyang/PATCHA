<?php
// /pages/pdf_uploader.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 관리자만 접근 가능하도록 설정
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    die("권한이 없습니다.");
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>PDF를 이미지로 변환</title>
    <style>
        body { font-family: sans-serif; background-color: #333; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background-color: #444; padding: 30px; border-radius: 8px; }
        h1 { text-align: center; border-bottom: 1px solid #666; padding-bottom: 20px; }
        label { font-size: 1.2em; margin-bottom: 10px; display: block; }
        input[type="file"] { background-color: #555; padding: 10px; border-radius: 5px; width: 100%; box-sizing: border-box; }
        button { font-size: 1.2em; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; display: block; width: 100%; margin-top: 20px; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>PDF 페이지별 이미지 변환기</h1>
        <p>이곳에서 PDF 파일을 올리면 모든 페이지가 이미지로 변환됩니다.<br>변환된 이미지의 HTML 코드를 복사하여 Summernote 에디터의 '코드 보기'에 붙여넣으세요.</p>
        <form action="pdf_to_images.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pdf_file">변환할 PDF 파일 선택:</label>
                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" required>
            </div>
            <button type="submit">이미지로 변환하기</button>
        </form>
    </div>
</body>
</html>