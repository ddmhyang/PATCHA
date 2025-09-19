<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 로그인</title>
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f0f0; }
        .login-box { padding: 40px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        input { display: block; width: 250px; padding: 10px; margin: 10px auto; }
        button { width: 270px; padding: 10px; background-color: #333; color: #fff; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>관리자 로그인</h2>
        <form id="loginForm">
            <input type="text" id="admin_id" name="admin_id" placeholder="아이디" required>
            <input type="password" id="admin_pass" name="admin_pass" placeholder="비밀번호" required>
            <button type="submit">로그인</button>
        </form>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        fetch('actions/login_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('로그인 성공!');
                window.location.href = 'index.php';
            } else {
                alert('아이디 또는 비밀번호가 일치하지 않습니다.');
            }
        })
        .catch(error => console.error('Error:', error));
    });
    </script>
</body>
</html>