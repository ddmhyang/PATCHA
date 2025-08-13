<?php
require_once '../includes/db.php';
$messages = $mysqli->query("SELECT * FROM chan_chat ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="chat-container">
    <div class="chat-window" id="message-list">
        <?php foreach ($messages as $msg): ?>
        <div class="message-row <?php echo strtolower(htmlspecialchars($msg['character_name'])); ?>">
            <div class="message-bubble">
                <div class="character-name"><?php echo htmlspecialchars($msg['character_name']); ?></div>
                <p class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($is_admin): ?>
    <div class="chat-input-area">
        <form id="chat-form" action="ajax_save_chat.php" method="post">
            <select name="character_name">
                <option value="hyun">Hyun</option>
                <option value="chan">Chan</option>
            </select>
            <input type="text" name="message" placeholder="메시지 입력..." autocomplete="off" required>
            <button type="submit">전송</button>
        </form>
    </div>
    <script>
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            type: 'POST', url: form.attr('action'), data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#content').load('chat.php');
                } else {
                    alert('전송 실패: ' + response.message);
                }
            }
        });
    });
    $('#message-list').scrollTop($('#message-list')[0].scrollHeight);
    </script>
    <?php endif; ?>
</div>