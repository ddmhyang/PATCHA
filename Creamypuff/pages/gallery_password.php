<div class="password-form-container">
    <div class="password_content">
        <h2>비밀글입니다</h2>
        <p>비밀번호를 입력해주세요.</p>
        <form id="password-form" action="ajax_verify_password.php" method="post">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <input type="password" name="password" required>
            <button type="submit">확인</button>
        </form>
        <div id="password-error" style="color:red; margin-top:10px;"></div>
    </div>
</div>
<script>
// 비밀번호 폼의 submit 이벤트가 발생하면 실행됩니다.
$('#password-form').on('submit', function(e) {
    e.preventDefault(); // 페이지 새로고침을 막습니다.
    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: $(this).serialize(), // post_id와 password를 전송합니다.
        dataType: 'json',
        success: function(response) {
            if (response.success) { // 서버로부터 'success: true' 응답을 받으면
                location.reload();    // 페이지를 새로고침합니다. (gallery_view.php가 다시 로드되면서 접근 권한이 생김)
            } else { // 실패하면
                $('#password-error').text(response.message); // 오류 메시지를 표시합니다.
            }
        },
        error: () => $('#password-error').text('오류가 발생했습니다.')
    });
});
</script>