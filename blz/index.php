<?php
// /blz/index.php (최종 수정본)
require_once 'includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>BLZ</title>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="nav_btn" data-page="main">
                    <div class="nav-default"></div>
                    <div class="nav-active"><div class="nav-active-ring"></div><div class="nav-active-dot"></div></div>
                </div>
                <div class="nav_btn" data-page="blz">
                    <div class="nav-default"></div>
                    <div class="nav-active"><div class="nav-active-ring"></div><div class="nav-active-dot"></div></div>
                </div>
                <div class="nav_btn" data-page="art">
                    <div class="nav-default"></div>
                    <div class="nav-active"><div class="nav-active-ring"></div><div class="nav-active-dot"></div></div>
                </div>
                <div class="nav_btn" data-page="novel">
                    <div class="nav-default"></div>
                    <div class="nav-active"><div class="nav-active-ring"></div><div class="nav-active-dot"></div></div>
                </div>
                
                <?php if ($is_admin): ?>
                    <a href="logout.php" class="nav_btn_link">
                        <div class="nav_btn nav-login-btn" data-page="logout">
                            <div class="nav-default"></div>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="nav_btn" data-page="login">
                        <div class="nav-default"></div>
                        <div class="nav-active"><div class="nav-active-ring"></div><div class="nav-active-dot"></div></div>
                    </div>
                <?php endif; ?>
            </nav>
        </header>

        <main id="content"></main>
    </div>

    <script>
        const isAdmin = <?php echo json_encode($is_admin); ?>;
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>