<?php
// /pages/main.php

// [수정 1] 상위 폴더의 db.php를 참조하도록 경로를 수정합니다.
require_once '../includes/db.php'; 

// 기본 로그인 확인
if (!isset($_SESSION['player_logged_in']) || $_SESSION['player_logged_in'] !== true) {
    // index.php는 상위 폴더에 있으므로 '../'를 사용합니다.
    header('Location: ../index.php');
    exit;
}

// 관리자 로그인 여부 확인
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// 불러올 페이지 결정
$page = $_GET['page'] ?? 'home'; 

// 허용된 페이지 목록
$allowed_pages = [
    'home', 'page_view', 
    'gallery1', 'gallery2', 'gallery_etc',
    'gallery_view', 'gallery_upload', 'gallery_edit',
    'search'
];

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eden</title>

    <link rel="stylesheet" href="../assets/css/main-style.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

</head>
<body>

    <header>
        <div class="search-area">
            <form action="main.php" method="get">
                <input type="hidden" name="page" value="search">
                <input type="text" name="query" placeholder="게시물 검색..." required>
                <button type="submit">검색</button>
            </form>
        </div>

        <nav>
            <a href="main.php?page=page_view&name=eden">EDEN</a>
            <a href="main.php?page=page_view&name=white">WHITE</a>
            <a href="main.php?page=page_view&name=rivlen">RIVLEN</a>
            <a href="main.php?page=page_view&name=timeline">TIMELINE</a>
            <a href="main.php?page=page_view&name=trpg">TRPG</a>
            <a href="main.php?page=gallery1">GALLERY 1</a>
            <a href="main.php?page=gallery2">GALLERY 2</a>
            <a href="main.php?page=gallery_etc">ETC GALLERY</a>
            |
            <?php if ($is_admin): ?>
                <a href="logout.php">전체 로그아웃</a>
            <?php else: ?>
                <a href="login.php">관리자 로그인</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <?php
        // [수정 3] 페이지 포함 경로를 수정합니다.
        // main.php와 같은 폴더에 있는 파일들을 불러오므로 'pages/' 부분을 제거합니다.
        if ($page === 'home') {
            // 홈 화면일 경우 eden 페이지를 보여줌
            $_GET['name'] = 'eden'; // page_view.php가 사용할 파라미터 설정
            include 'page_view.php';
        } elseif (in_array($page, $allowed_pages)) {
            $page_file = $page . '.php'; // 'pages/' 제거
            if (file_exists($page_file)) {
                include $page_file;
            } else {
                echo '<h1>페이지 파일을 찾을 수 없습니다.</h1>';
            }
        } else {
            echo '<h1>페이지를 찾을 수 없습니다.</h1>';
        }
        ?>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>