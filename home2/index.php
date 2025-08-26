<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>Timeline Gallery</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <?php if ($is_admin):?>
    <style>
        #timeline-line {
            cursor: copy;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <nav>
            <a href="#/timeline">전체 타임라인</a>
            <a href="#/novel_timeline">소설</a>
            <a href="#/roleplay_timeline">역극 백업</a>
            <a href="#/trpg_timeline">TRPG 로그</a>
            <hr>
            <?php if ($is_admin): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Admin Login</a>
            <?php endif; ?>
        </nav>
        <main id="content"></main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>