<?php



require_once __DIR__ . '/../includes/db.php';


if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: main.php');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        
        $sql = "SELECT id, username, password_hash FROM users WHERE username = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            
            if (password_verify($password, $user['password_hash'])) {
                
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id']; 
                
                
                header('Location: main.php');
                exit;
            }
        }
    }
    
    
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