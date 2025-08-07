<?php
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>홈페이지</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body>
    <header>
        <nav>
            <a href="#/home">Home</a>
            <a href="#/char1">Char1</a>
            <a href="#/char2">Char2</a>
            <a href="#/gallery">Gallery</a>
            <a href="#/sns">SNS</a>
            <a href="#/au">AU</a>
            <a href="#/trpg">TRPG</a>
            <a href="#/note">Note</a>
            <?php if ($is_admin): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Admin Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main id="content"></main>
    <script src="../assets/js/main.js"></script>
</body>
</html>