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
        <form id="login-form" action="ajax_login.php" method="post">
            <input type="text" name="username" required>
            <input type="password" name="password" required>
            <button type="submit">로그인</button>
            <div id="login-error" style="color:red; margin-top:10px;"></div>
        </form>
    </div>
</div>
<script>
// 로그인 폼의 submit 이벤트가 발생하면 실행됩니다.
$('#login-form').on('submit', function(e) {
    e.preventDefault(); // 페이지 새로고침을 막습니다.
    $.ajax({
        url: $(this).attr('action'), // 폼의 action 속성에 지정된 'ajax_login.php'로 요청
        type: 'POST',
        data: $(this).serialize(), // 폼 안의 모든 입력 데이터를(username, password) 전송
        dataType: 'json',
        success: function(response) {
            if (response.success) { // 서버로부터 'success: true' 응답을 받으면
                window.location.href = 'main.php'; // main.php로 페이지를 완전히 새로고침하여 이동합니다.
            } else { // 실패 응답을 받으면
                $('#login-error').text(response.message); // 오류 메시지를 화면에 표시합니다.
            }
        },
        error: () => $('#login-error').text('로그인 중 서버 오류가 발생했습니다.')
    });
});
</script>