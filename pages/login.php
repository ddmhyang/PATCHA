<?php
// /pages/login.php

// DB 연결, 세션 시작, CSRF 토큰 생성을 위해 db.php를 포함합니다.
require_once '../includes/db.php';

// 이미 관리자로 로그인했다면 main.php로 이동
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: main.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '잘못된 접근입니다. 페이지를 새로고침한 후 다시 시도해주세요.';
    } else {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // 2. 관리자 정보 조회
        $stmt = $mysqli->prepare("SELECT id, password_hash FROM eden_admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                // 3. 로그인 성공 처리
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                // 보안 강화: 세션 고정 공격 방지를 위해 세션 ID 재생성 및 토큰 재발급
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: main.php');
                exit;
            }
        }
        $error = '아이디 또는 비밀번호가 잘못되었습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 로그인</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" name="username" placeholder="아이디" required>
            <input type="password" name="password" placeholder="비밀번호" required>
            <button type="submit">로그인</button>
        </form>
        <?php if ($error): ?>
            <p class="error" style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>