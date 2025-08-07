<?php
require_once '../includes/db.php';
$messages = $mysqli->query("SELECT * FROM home_sns ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="chat-container">
    <div class="chat-header">> Character Chat</div>
    <div class="chat-window" id="message-list">
        <?php foreach ($messages as $msg): ?>
        <div class="message-row <?php echo strtolower(htmlspecialchars($msg['character_name'])); ?>">
            <div class="message-bubble">
                <div class="character-name"><?php echo htmlspecialchars($msg['character_name']); ?></div>
                <p class="message-text"><?php echo nl2br(htmlspecialchars($msg['message_text'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($is_admin): ?>
    <div class="chat-input-area">
        <form id="sns-form" action="ajax_save_sns.php" method="post">
            <select name="character_name">
                <option value="Char1">캐릭터1</option>
                <option value="Char2">캐릭터2</option>
            </select>
            <input type="text" name="message_text" placeholder="메시지 입력..." autocomplete="off" required>
            <button type="submit">전송</button>
        </form>
    </div>
    <?php endif; ?>
</div>
<script>
$(document).ready(function() {
    var chatWindow = $('#message-list');
    chatWindow.scrollTop(chatWindow[0].scrollHeight);

    $('#sns-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            type: 'POST', url: form.attr('action'), data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('전송 실패: ' + response.message);
                }
            }
        });
    });
});
</script>