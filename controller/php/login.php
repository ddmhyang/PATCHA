<?php
/*
 * login.php (Bcrypt / password_verify() 사용 버전)
 * (★ SQLite와 호환됨)
 */
session_start(); // 세션 시작
include 'db_connect.php'; // (SQLite용 db_connect.php를 불러옴)

$error_message = '';

// 1. 폼이 제출되었는지 확인 (POST 방식)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            // 2. DB에서 'username'으로 관리자 정보 조회
            $sql = "SELECT * FROM youth_admin_users WHERE username = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $admin_user = $stmt->fetch(); // 1개의 행만 가져옴

            // 3. (★핵심★) 사용자가 존재하고, password_verify()로 비밀번호가 일치하는지 검사
            if ($admin_user && password_verify($password, $admin_user['password_hash'])) {
                
                // 4. 일치하면, 세션에 '로그인 성공' 기록
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin_user['username'];
                
                // 5. 관리자 메인 페이지(index.php)로 이동
                header("Location: index.php");
                exit;
            
            } else {
                // 6. 실패하면 에러 메시지
                $error_message = "아이디 또는 비밀번호가 틀렸습니다.";
            }

        } catch (PDOException $e) {
            $error_message = "데이터베이스 오류: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 로그인</title>
    <style>
        /* (CSS 스타일은 동일) */
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 100vh; }
        form { border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { padding: 8px; width: 250px; }
        .error { color: red; }
    </style>
</head>
<body>
    <form action="login.php" method="POST">
        <h2>밴드 상점 관리자 로그인</h2>
        <div class="form-group">
            <label for="username">아이디</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">비밀번호</label>
            <input type="password" id="password" name="password" required>
        </div>
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <button type="submit">로그인</button>
    </form>
</body>
</html>