<?php
require_once '../includes/db.php';

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

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <link
            href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css"
            rel="stylesheet">
        <script
            src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <link rel="stylesheet" href="../assets/css/style.css">
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
                <svg
                    class="main_chang"
                    xmlns="http://www.w3.org/2000/svg"
                    width="225"
                    height="45"
                    viewbox="0 0 225 45"
                    fill="none">
                    <rect x="-0.00683594" width="45" height="45" fill="#FAFAFA"/>
                    <rect x="89.9932" width="45" height="45" fill="#FAFAFA"/>
                    <rect x="179.993" width="45" height="45" fill="#FAFAFA"/>
                </svg>
                <main class="main_box" id="content-container"></main>

            </div>

            <div class="bottom_bar">
                <svg
                    class="bottom_pre_btn"
                    xmlns="http://www.w3.org/2000/svg"
                    width="80"
                    height="50"
                    viewbox="0 0 80 50"
                    fill="none">
                    <path d="M30 25L67.5 46.6506V3.34937L30 25Z" fill="#FAFAFA"/>
                    <path d="M0 25L37.5 46.6506V3.34937L0 25Z" fill="#FAFAFA"/>
                </svg>
                <svg
                    class="bottom_play_btn"
                    xmlns="http://www.w3.org/2000/svg"
                    width="38"
                    height="44"
                    viewbox="0 0 38 44"
                    fill="none">
                    <path d="M38 22L0.5 43.6506V0.349365L38 22Z" fill="#FAFAFA"/>
                </svg>
                <svg
                    class="bottom_next_btn"
                    xmlns="http://www.w3.org/2000/svg"
                    width="80"
                    height="50"
                    viewbox="0 0 80 50"
                    fill="none">
                    <path d="M50 25L12.5 46.6506V3.34937L50 25Z" fill="#FAFAFA"/>
                    <path d="M80 25L42.5 46.6506V3.34937L80 25Z" fill="#FAFAFA"/>
                </svg>
            </div>

            <div
                id="messenger-overlay"
                style="display: none; position: absolute; left: 829px; top: 99px; z-index: 100;"></div>

            <audio id="bgm-player" loop="loop">
                <source src="../assets/bgm/Left on our hands  DOFOLIL Themes.mp3" type="audio/mpeg">
            </audio>
        </div>

        <script>
            const csrfToken = '<?php echo $csrf_token; ?>';
            const isLoggedIn = <?php echo $is_admin ? 'true' : 'false'; ?>;
        </script>
        <script src="../assets/js/main.js"></script>
    </body>
</html>