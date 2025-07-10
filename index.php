<?php
// /index.php

require_once 'includes/db.php';

if (isset($_SESSION['player_logged_in']) && $_SESSION['player_logged_in'] === true) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy(); 
    header('Location: index.php');
    exit;
}


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '잘못된 접근입니다. 페이지를 새로고침한 후 다시 시도해주세요.';
    } elseif (isset($_POST['password']) && $_POST['password'] === '1234') {
        $_SESSION['player_logged_in'] = true;
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: pages/main.php');
        exit;
    } else {
        $error = '비밀번호가 올바르지 않습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eden</title>
        <style>
            @font-face {
                font-family: 'Bonheur-Royale';
                src: url("assets/fonts/Bonheur-Royale.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre1';
                src: url("assets/fonts/Freesentation-1Thin.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre9';
                src: url("assets/fonts/Freesentation-9Black.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            body,
            html {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                background-color: rgb(0, 0, 0);
                overflow: hidden;
                position: relative;
                visibility: hidden;
                font-family: 'S-CoreDream-3Light', sans-serif;
            }

            .container {
                width: 1440px;
                height: 900px;
                background-color: #000000;
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
            }

            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }

            .login-container {
                width: 1440px;
                height: 900px;
                flex-shrink: 0;
                aspect-ratio: 1440/900;
                background: url("assets/img/background.png") rgb(0, 0, 0) 50% / cover no-repeat;
            }

            .title {
                color: #FFF;
                text-align: center;
                font-family: "Bonheur-Royale";
                font-size: 96px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                position: absolute;
                left: 50%;
                top: 168px;
                transform: translateX(-50%);
            }

            .index_submit {
                width: 201px;
                height: 52px;
                flex-shrink: 0;
                color: #FFF;
                text-align: center;
                font-family: "Freesentation9";
                font-size: 20px;
                font-style: normal;
                font-weight: 900;
                line-height: normal;
            }

            .index_login {
                position: absolute;
                left: 50%;
                top: 576px;
                transform: translateX(-50%);
                width: 409px;
                height: 84px;
                flex-shrink: 0;
                background: rgba(255, 255, 255, 0.50);
                border: none;
                text-align: center;
                font-size: 24px;

            }

            .index_login:focus {
                outline: none;
            }

            .index_submit {
                position: absolute;
                left: 50%;
                top: 685px;
                transform: translateX(-50%);
                width: 201px;
                height: 52px;
                flex-shrink: 0;
                background: rgba(255, 255, 255, 0.50);
                cursor: pointer;
                border: none;
                transition-duration: 0.25s;
            }

            .index_submit:hover {
                transform: translateX(-50%) scale(1.05);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="login-container">

                <div class="title">EDEN</div>

                <form method="post" action="index.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input class="index_login" type="password" name="password" required="required">
                    <button class="index_submit" type="submit">Login</button>
                </form>
                <?php if ($error): ?>
                <p class="error" style="color:red;"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
        </div>

        <script>

            function adjustScale() {
                const container = document.querySelector('.container');
                if (!container) 
                    return;
                
                let containerWidth,
                    containerHeight;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                if (windowWidth <= 784) {
                    containerWidth = 720;
                    containerHeight = 1280;
                } else {
                    containerWidth = 1440;
                    containerHeight = 900;
                }

                const scale = Math.min(
                    windowWidth / containerWidth,
                    windowHeight / containerHeight
                );
                container.style.transform = `scale(${scale})`;
                container.style.left = `${ (windowWidth - containerWidth * scale) / 2}px`;
                container.style.top = `${ (windowHeight - containerHeight * scale) / 2}px`;

            }

            window.addEventListener('load', () => {
                adjustScale();
                document.body.style.visibility = 'visible';
            });
            window.addEventListener('resize', adjustScale);
        </script>
    </body>
</html>