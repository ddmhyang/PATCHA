<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로그인 기능 단독 테스트</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* 페이지 스타일을 여기에 직접 넣어서 외부 파일 의존성을 완전히 제거합니다 */
        body { background-color: #333; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
        .login-container { text-align: center; }
        #login_username, #login_password, #login_submit { position: relative; margin-bottom: 10px; }
        input, button { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: transparent; border: none; color: white; font-size: 20px; text-align: center; box-sizing: border-box; padding: 0 20px; }
        button { cursor: pointer; }
    </style>
</head>
<body>

<div class="login-container">
    <h1>로그인 테스트</h1>
    <form id="login-form">
        <div id="login_username">
            <svg width="452" height="102" viewBox="0 0 452 102" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="1" width="450" height="100" rx="10" fill="black" fill-opacity="0.5" stroke="#DEB200" stroke-width="2"/>
            </svg>
            <input type="text" name="username" placeholder="Username" required>
        </div>

        <div id="login_password">
            <svg width="452" height="102" viewBox="0 0 452 102" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="1" width="450" height="100" rx="10" fill="black" fill-opacity="0.5" stroke="#DEB200" stroke-width="2"/>
            </svg>
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div id="login_submit">
             <svg width="260" height="69" viewBox="0 0 260 69" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="1" width="258" height="67" rx="10" fill="black" fill-opacity="0.5" stroke="#DEB200" stroke-width="2"/>
            </svg>
            <button type="submit">Login</button>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    // 1. 페이지가 준비되면 F12 콘솔에 이 메시지가 보여야 합니다.
    console.log("독립 테스트 페이지의 스크립트가 준비되었습니다.");

    $('#login-form').on('submit', function(e) {
        e.preventDefault(); 
        
        // 2. 버튼을 누르면 무조건 이 알림창이 떠야 합니다.
        alert("버튼 클릭 성공! 이제 서버와 통신합니다.");

        // 3. 알림창이 뜬 후에 실제 로그인 통신을 시도합니다.
        $.ajax({
            type: 'POST',
            url: 'ajax_login.php', // 이 파일은 sHotel 폴더에 있어야 합니다.
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert('로그인 성공!');
                    // 여기서는 다른 페이지로 이동하지 않습니다.
                } else {
                    alert('로그인 실패: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('서버 통신 오류! F12 콘솔을 확인하세요.');
                console.error("서버 응답:", xhr.responseText);
            }
        });
    });
});
</script>

</body>
</html>