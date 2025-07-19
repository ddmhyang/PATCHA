<?php
// --- 파일 경로: /pages/login.php ---

// DB 연결 및 세션 시작을 위해 가장 먼저 호출합니다.
require_once __DIR__ . '/../includes/db.php';

// 만약 이미 로그인 상태라면, main.php로 즉시 이동시킵니다.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: main.php');
    exit;
}

$error_message = '';
// 사용자가 폼을 통해 아이디와 비밀번호를 제출했을 때
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // 1. 데이터베이스에서 'admin' 사용자를 찾습니다.
        $sql = "SELECT id, username, password_hash FROM users WHERE username = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 2. DB에 저장된 암호화된 비밀번호와 사용자가 입력한 '1234'가 일치하는지 확인합니다.
            if (password_verify($password, $user['password_hash'])) {
                // 3. 로그인 성공! 세션에 중요한 정보들을 저장합니다.
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id']; // 이 값이 있어야 글쓰기/수정/삭제 권한이 생깁니다.
                
                // 메인 페이지로 이동합니다.
                header('Location: main.php');
                exit;
            }
        }
    }
    
    // 로그인에 실패한 경우 에러 메시지를 설정합니다.
    $error_message = '아이디 또는 비밀번호가 올바르지 않습니다.';
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 로그인 - DolfoLil</title>
    <style>
        @font-face {
            font-family: 'DungGeunMo';
            src: url("../assets/fonts/DungGeunMo.ttf") format('truetype');
        }
        @font-face {
            font-family: 'Galmuri9';
            src: url("../assets/fonts/Galmuri9.ttf") format('truetype');
        }
        body { 
            background-color: #000; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
            font-family: 'Galmuri9', sans-serif;
        }
        .login-form { 
            padding: 40px; 
            background: #1A1A1A; 
            border-radius: 10px; 
            color: #FAFAFA; 
            width: 300px; 
            text-align: center; 
        }
        .login-form h1 { 
            font-family: 'DungGeunMo'; 
            font-size: 24px; 
        }
        .login-form input { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 15px; 
            border: 1px solid #333; 
            background: #222; 
            color: #fff; 
            box-sizing: border-box; 
        }
        .login-form button { 
            width: 100%; 
            padding: 10px; 
            background: #FAFAFA; 
            color: #1A1A1A; 
            border: none; 
            cursor: pointer; 
            font-weight: bold; 
        }
        .error { 
            color: #ff6b6b; 
            margin-top: 15px; 
        }
    </style>
</head>
<body>
    <form class="login-form" method="POST" action="login.php">
        <h1>관리자 로그인</h1>
        <input type="text" name="username" placeholder="아이디" value="admin" required>
        <input type="password" name="password" placeholder="비밀번호" required>
        <button type="submit">로그인</button>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
    </form>
</body>
</html>