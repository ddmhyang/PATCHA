<?php
// /pages/login.php
require_once '../includes/db.php';


if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: main.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '잘못된 접근입니다. 페이지를 새로고침한 후 다시 시도해주세요.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        
        $stmt = $mysqli->prepare("SELECT id, password_hash FROM eden_admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: main.php');
                exit;
            }
        }
        $error = '아이디 또는 비밀번호가 잘못되었습니다.';
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token']; 
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>관리자 로그인</title>
        <style>
            @font-face {
                font-family: 'Bonheur-Royale';
                src: url("../assets/fonts/Bonheur-Royale.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre1';
                src: url("../assets/fonts/Freesentation-1Thin.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre9';
                src: url("../assets/fonts/Freesentation-9Black.ttf") format('truetype');
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
               
                font-family: 'Fre1', sans-serif;
               
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
                background: url("../assets/img/background.png") rgb(0, 0, 0) 50% / cover no-repeat;
                display: flex;
               
                flex-direction: column;
                align-items: center;
                justify-content: center;
                position: relative;
               
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
                top: 168px;
               
               
            }

            form {
                position: absolute;
                top: 440px;
               
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 30px;
               
            }

            input[type="text"],
            input[type="password"] {
                width: 409px;
                height: 84px;
                flex-shrink: 0;
                background: rgba(255, 255, 255, 0.50);
                border: none;
                text-align: center;
                font-family: "Fre9";
               
                font-size: 24px;
                color: #000000;
               
                padding: 0 20px;
               
                box-sizing: border-box;
               
            }

            input[type="text"]::placeholder,
            input[type="password"]::placeholder {
                color: rgba(255, 255, 255, 0.7);
               
            }

            input:focus {
                outline: none;
            }

            button[type="submit"] {
                width: 201px;
                height: 52px;
                flex-shrink: 0;
                color: #FFF;
                text-align: center;
                font-family: "Fre9";
                font-size: 20px;
                font-style: normal;
                font-weight: 900;
                line-height: normal;
                background: rgba(255, 255, 255, 0.50);
                cursor: pointer;
                border: none;
                transition-duration: 0.25s;
            }

            button[type="submit"]:hover {
                transform: scale(1.05);
            }

            .error {
                color: red;
                font-family: 'Fre1', sans-serif;
                font-size: 18px;
                margin-top: 20px;
               
                position: absolute;
                top: 750px;
               
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="login-container">

                <div class="title">EDEN</div>

                <form method="post" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="text" name="username" required="required">
                    <input type="password" name="password" required="required">
                    <button type="submit">Login</button>
                </form>
                <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
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