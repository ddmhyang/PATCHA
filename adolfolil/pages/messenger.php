<?php
// --- 파일 경로: /pages/messenger.php ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

$messages = $mysqli->query("SELECT * FROM messages ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>
<style>
    
</style>
<div class="phone_border">
    <div class="phone_back">
        <div id="message-list">
            <?php foreach ($messages as $msg): ?>
                <div class="message-item <?php echo ($msg['character_name'] === 'Adolfo') ? 'left' : 'right'; ?>" data-id="<?php echo $msg['id']; ?>">
                    <div class="phone_profile" style="background-image: url('../assets/images/<?php echo ($msg['character_name'] === 'Adolfo') ? 'torken.png' : 'torken2.png'; ?>')"></div>
                    <div class="phone_chat">
                        <a><?php echo htmlspecialchars($msg['message_text']); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="phone_bottom">
            <form id="messenger-form" action="actions/messenger_save.php" method="post">
                <select name="character">
                    <option value="Adolfo">Adolfo</option>
                    <option value="Lilian">Lilian</option>
                </select>
                <input type="text" name="message" placeholder="메시지 입력..." required>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit">전송</button>
            </form>
        </div>
    </div>
    <a class="phone_title">> DolfoLil</a>
</div>

<?php if ($is_admin): ?>
<script>
// 메시지를 길게 눌렀을 때 삭제 로직
let pressTimer;
$('#message-list .message-item').on('mousedown', function() {
    let messageElement = $(this);
    pressTimer = window.setTimeout(function() {
        if (confirm('이 메시지를 삭제하시겠습니까?')) {
            let messageId = messageElement.data('id');
            $.post('actions/messenger_delete.php', { id: messageId, csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' }, function(response) {
                if (response.success) {
                    messageElement.fadeOut(300, function() { $(this).remove(); });
                } else {
                    alert('삭제 실패: ' + (response.message || '알 수 없는 오류'));
                }
            }, 'json');
        }
    }, 800); // 0.8초간 누르면 삭제 확인창 표시
}).on('mouseup mouseleave', function() {
    clearTimeout(pressTimer);
});
</script>
<?php endif; ?>