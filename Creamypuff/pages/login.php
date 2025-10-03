<?php
require_once '../includes/db.php';
// 만약 이미 관리자로 로그인된 상태라면, 메인 페이지로 이동시킵니다.
if ($is_admin) {
    echo "<script>window.location.hash = '#/main';</script>";
    exit;
}
?>
<div class="login-container">
    <div class="login-form">
        <form class="ajax-form" id="login-form" action="ajax_login.php" method="post">
            <input type="text" name="username" required>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
            <div id="login-error" style="color:red; margin-top:10px;"></div>
        </form>
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
                window.location.href = 'main.php';
            } else {
                $('#login-error').text(response.message);
            }
        },
        error: () => $('#login-error').text('로그인 중 서버 오류가 발생했습니다.')
    });
});
</script>