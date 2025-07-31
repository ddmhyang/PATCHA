<?php
session_start();
if (isset($_SESSION['blz_logged_in']) && $_SESSION['blz_logged_in'] === true) {
    header('Location: pages/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_attempt = $_POST['password'] ?? '';
    if ($password_attempt === '1213') {
        $_SESSION['blz_logged_in'] = true;
        header('Location: pages/index.php');
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
    <title>BLZ - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            text-align: center;
        }
        .login-box input[type="password"] {
            display: block;
            width: 400px;
            height: 80px;
            border-radius: 30px;
            border: 4px solid #FFF;
            background: rgba(255, 255, 255, 1);
            text-align: center;
            font-size: 20px;
            margin-bottom: 30px;
            color: #000000;
        }
        .login-box button {
            width: 220px;
            height: 70px;
            color: #000;
            background: #fff;
            border: 4px solid #FFF;
            border-radius: 30px;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
        }
        .login-error {
            color: #ffcccc;
            margin-top: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-wrapper">
            <div class="login-box">
                <form method="POST" action="index.php">
                    <input type="password" name="password" placeholder="Password" required autofocus>
                    <button type="submit">ENTER</button>
                </form>
                <?php if ($error): ?>
                    <p class="login-error"><?php echo $error; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>