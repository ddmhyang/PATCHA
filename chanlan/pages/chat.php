<?php
require_once '../includes/db.php';
// 시간 순서대로 메시지를 가져옵니다.
$messages = $mysqli->query("SELECT * FROM chan_chat ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="chat-container">
    <div class="chat-window" id="message-list">
        <?php foreach ($messages as $msg): ?>
        <?php
            // 캐릭터 이름에 따라 'sent'(오른쪽) 또는 'received'(왼쪽) 클래스를 부여합니다.
            // 'chan' 캐릭터를 기준으로 'sent'로 설정합니다.
            $message_side_class = (strtolower($msg['character_name']) === 'chan') ? 'sent' : 'received';
        ?>
        <div class="message-row <?php echo $message_side_class; ?>" data-id="<?php echo $msg['id']; ?>">
            <div class="profile-pic <?php echo strtolower(htmlspecialchars($msg['character_name'])); ?>"></div>
            <div class="message-bubble">
                <div class="character-name"><?php echo htmlspecialchars($msg['character_name']); ?></div>
                <p class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>

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
        <?php endif; ?>
    </div>

</div>

<script>
$(document).ready(function() {
    // 채팅창이 로드될 때 항상 스크롤을 맨 아래로 이동
    var chatWindow = $('#message-list');
    chatWindow.scrollTop(chatWindow[0].scrollHeight);

    // 채팅 폼 제출 이벤트
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // 성공 시 채팅창 내용만 새로고침하여 즉시 메시지 확인
                    $('#content').load('chat.php');
                } else {
                    alert('메시지 전송 실패: ' + response.message);
                }
            }
        });
    });

    // 관리자일 경우 메시지 꾹 눌러서 삭제하는 기능
    <?php if ($is_admin): ?>
    let pressTimer;
    $(document).on('mousedown', '.message-row', function() {
        let messageRow = $(this);
        pressTimer = window.setTimeout(function() {
            if (confirm('이 메시지를 삭제하시겠습니까?')) {
                const messageId = messageRow.data('id');
                $.ajax({
                    url: 'ajax_delete_chat.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            messageRow.fadeOut(300, function() { $(this).remove(); });
                        } else {
                            alert('삭제 실패: ' + response.message);
                        }
                    }
                });
            }
        }, 800); // 0.8초간 누르고 있으면 삭제 확인 창 표시
    }).on('mouseup mouseleave', '.message-row', function() {
        clearTimeout(pressTimer);
    });
    <?php endif; ?>
});
</script>