<?php
// /index.php

// CSRF 토큰 생성 및 세션 시작을 위해 db.php를 포함합니다.
require_once 'includes/db.php';

// =================== 변경된 부분 시작 ===================

// 로그인된 상태에서 접근 시, 로그아웃 처리 후 현재 페이지(index.php)로 새로고침합니다.
if (isset($_SESSION['player_logged_in']) && $_SESSION['player_logged_in'] === true) {
    
    // 기존 로그아웃 로직을 그대로 사용하여 세션을 완전히 파괴합니다.
    $_SESSION = array(); // 모든 세션 변수 지우기

    // 세션 쿠키 삭제
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy(); // 세션 파괴

    // 깨끗한 세션 상태로 index.php를 다시 로드하기 위해 리디렉션합니다.
    header('Location: index.php');
    exit;
}

// =================== 변경된 부분 끝 ===================


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = '잘못된 접근입니다. 페이지를 새로고침한 후 다시 시도해주세요.';
    } elseif (isset($_POST['password']) && $_POST['password'] === '1234') {
        // 2. 비밀번호 확인 및 로그인 처리
        $_SESSION['player_logged_in'] = true;
        // 로그인 성공 시 보안을 위해 세션 ID를 재생성하고 토큰을 재발급합니다.
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
    <title>Eden - 입장</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>ACCESS</h1>
        <form method="post" action="index.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="password" name="password" placeholder="비밀번호를 입력하세요" required>
            <button type="submit">입장</button>
        </form>
        <?php if ($error): ?>
            <p class="error" style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>