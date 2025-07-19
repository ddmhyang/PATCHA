<?php
// --- 파일 경로: /pages/main.php ---
require_once '../includes/db.php';

// 'eden' 프로젝트와 동일하게 세션 변수명을 사용합니다.
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DolfoLil</title>
    <link rel="icon" type="image/png" href="../assets/images/logo1.jpg">
    <link rel="stylesheet" href="../assets/css/style.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="sub_menu_border">
            <a href="login.php" class="sMChang-link" title="관리자 로그인/로그아웃">
                 <div class="sMChang"></div>
            </a>
            <div class="sub_menu">
                <a href="#/main_content" data-page="main_content">Main</a>
                <a href="#/dolfolil" data-page="dolfolil">DolfoLil</a>
                <a href="#/gallery" data-page="gallery">Gallery</a>
                <a href="#/trpg" data-page="trpg">TRPG</a>
                <a href="#/messenger" data-page="messenger">Messenger</a>
                <div class="sMLine"></div>
            </div>
        </div>

        <div class="main_border">
            <a href="../index.php">DolfoLil</a>
            <main class="main_box" id="content-container"></main>
        </div>

        <div class="bottom_bar">
            </div>
    </div>
    
    <script>
        // PHP 변수를 JavaScript 전역 변수로 선언합니다.
        const csrfToken = '<?php echo $csrf_token; ?>';
    </script>
    <script src="../assets/js/main.js"></script>
</body>
</html>