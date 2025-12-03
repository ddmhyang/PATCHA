<?php
require_once '../includes/db.php';
if ($is_admin) {
    echo "<script>window.location.hash = '';</script>";
    exit;
}
?>


<div class="page-container" id="main_content">
    <div class="main-frame">
        <div class="left-section" id="login-left">
            <i class="fa-solid fa-key floating-icon fi-1"></i>
            <i class="fa-solid fa-feather-pointed floating-icon fi-2"
               style="left: 180px; bottom: 40px;"></i>

            <div class="sub-title">Login</div>
            <h1>System</h1>
            <a href="#/" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                메인으로 돌아가기
            </a>
        </div>

        <div class="login-container" id="login-right">
            <div class="login-box">
                <h2 class="login-title">Welcome Back!</h2>
                <form id="login-form" action="ajax_login.php" method="post">
                    <div class="input-group">
                        <input type="text" name="username" placeholder="아이디를 입력하세요" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" placeholder="비밀번호를 입력하세요" required>
                    </div>
                    <button type="submit">로그인</button>
                    <div id="login-error"></div>
                </form>
            </div>
        </div>
    </div>
</div>




<script>
$('#login-form').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                window.location.href = ''; 
            } else {
                $('#login-error').text(response.message);
            }
        },
        error: () => $('#login-error').text('로그인 중 서버 오류가 발생했습니다.')
    });
});
</script>