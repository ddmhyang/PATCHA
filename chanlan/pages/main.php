<?php require_once '../includes/db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>ChanLan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body>
    <header>
        <nav>
            <a href="#/main_content">Main</a>
            <a href="#/chanlan">ChanLan</a>
            <a href="#/hyun">Hyun</a>
            <a href="#/chan">Chan</a>
            <a href="#/chat">Chat</a>
            <a href="#/gallery">Gallery</a>
            <a href="#/trpg">TRPG</a>
            <?php if ($is_admin): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="#/login">Login</a>
            <?php endif; ?>
        </nav>
        <div class="d-day-counter">
            <?php
                $target_date = new DateTime("2025-06-11"); $current_date = new DateTime();
                $interval = $current_date->diff($target_date); $d_day = $interval->days;
                if ($interval->invert) { echo "D+" . $d_day; } else { echo "D-" . $d_day; }
            ?>
        </div>
    </header>
    <main id="content"></main>
    <script src="../assets/js/main.js"></script>
</body>
</html>