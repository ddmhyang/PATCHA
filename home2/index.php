<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">


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
            <div class="main-links">
                <a href="#/timeline">ALL</a>
                <a href="#/novel_timeline">Novel</a>
                <a href="#/roleplay_timeline">RP</a>
                <a href="#/trpg_timeline">TRPG</a>
                <?php if ($is_admin): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="#/login">Login</a>
                <?php endif; ?>
            </div>
        </nav>
        
        <main id="content">
            <div id="timeline-container" class="view">
                </div>

            <div id="login-view" class="view">
                <div class="login-container">
                    <h1>관리자 로그인</h1>
                    <form id="login-form" method="post">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit">로그인</button>
                        <p class="error" id="login-error"></p>
                    </form>
                </div>
            </div>

            <div id="ajax-content-view" class="view">
            </div>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>