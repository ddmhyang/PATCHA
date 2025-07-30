<?php
// /blz/login.php (수정된 코드)
require_once 'includes/db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT password_hash FROM blz_admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: index.php');
            exit;
        }
    }
    $error = '아이디 또는 비밀번호가 잘못되었습니다.';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>BLZ 관리자 로그인</title>
    <style>
        /* eden 프로젝트의 스타일을 이 페이지에 직접 적용 */
        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            background-color: #000; overflow: hidden; visibility: hidden;
        }
        .container {
            width: 1440px; height: 900px;
            background: url('assets/images/background.png') #1a1a1a 50% / cover no-repeat;
            position: absolute; transform-origin: top left;
        }
        .login-box {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        .title {
            color: #FFF; font-family: "Bonheur-Royale", sans-serif; /* 폰트 없으면 sans-serif로 대체 */
            font-size: 96px; margin-bottom: 50px;
        }
        form input {
            display: block; width: 409px; height: 84px;
            background: rgba(255, 255, 255, 0.5); border: none;
            text-align: center; font-size: 24px; color: #000;
            margin-bottom: 30px;
        }
        form button {
            width: 201px; height: 52px; color: #FFF;
            background: rgba(255, 255, 255, 0.5);
            border: none; cursor: pointer; font-size: 20px;
        }
        .error {
            color: red; margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="title">BLZ</div>
            <form method="post">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">LOGIN</button>
            </form>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // eden 프로젝트와 동일한 스케일 조정 스크립트
        function adjustScale() {
            const container = document.querySelector('.container');
            if (!container) return;
            const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
            const containerWidth = 1440, containerHeight = 900;
            const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
            container.style.transform = `scale(${scale})`;
            container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
            container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
        }
        // 페이지 로딩이 '완료'된 후 화면을 보이게 함
        window.addEventListener('load', () => {
            adjustScale();
            document.body.style.visibility = 'visible';
        });
        window.addEventListener('resize', adjustScale);
    </script>
</body>
</html>