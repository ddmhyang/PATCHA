<?php
require_once '../includes/db.php';

// 'chan_settings' 테이블에서 모든 설정 값을 가져옵니다.
$settings_result = $mysqli->query("SELECT * FROM chan_settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
// 설정에서 캐릭터 이름을 가져옵니다. 값이 없으면 기본 이름을 사용합니다.
$char1_name = $settings['character1_name'] ?? 'Hyun';
$char2_name = $settings['character2_name'] ?? 'Chan';
// 설정에서 캐릭터 프로필 이미지 경로를 가져옵니다.
$char1_img = $settings['character1_image'] ?? '/assets/img/default_hyun.png';
$char2_img = $settings['character2_image'] ?? '/assets/img/default_chan.png';

// 'chan_chat' 테이블에서 모든 메시지를 작성 시간 순으로 정렬하여 가져옵니다.
$messages = $mysqli->query("SELECT * FROM chan_chat ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="chat-container">
    <div class="chat-window" id="message-list">
        <div class="chat-header"></div>

        <?php foreach ($messages as $msg): ?>
        <?php
            // 현재 메시지의 캐릭터 이름이 설정의 캐릭터2 이름과 같은지 확인합니다. (대소문자 무시)
            $is_char2 = (strtolower($msg['character_name']) === strtolower($char2_name));
            // 캐릭터2이면 'sent'(보낸 메시지, 오른쪽 정렬), 아니면 'received'(받은 메시지, 왼쪽 정렬) 클래스를 적용합니다.
            $message_side_class = $is_char2 ? 'sent' : 'received';
            // 캐릭터에 맞는 프로필 이미지 경로를 설정합니다.
            $profile_image = $is_char2 ? $char2_img : $char1_img;
        ?>
        <div class="message-row <?php echo $message_side_class; ?>" data-id="<?php echo $msg['id']; ?>">
            <div class="profile-pic" style="background-image: url('..<?php echo htmlspecialchars($profile_image); ?>');"></div>
            <div class="message-bubble">
                <div class="character-name"><?php echo htmlspecialchars($msg['character_name']); ?></div>
                <p class="message-text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($is_admin): // 관리자에게만 메시지 입력 폼을 보여줍니다. ?>
    <div class="chat-input-area">
        <form id="chat-form" action="ajax_save_chat.php" method="post">
            <select name="character_name">
                <option value="<?php echo htmlspecialchars($char1_name); ?>"><?php echo htmlspecialchars($char1_name); ?></option>
                <option value="<?php echo htmlspecialchars($char2_name); ?>"><?php echo htmlspecialchars($char2_name); ?></option>
            </select>
            <input type="text" name="message" placeholder="메시지 입력..." autocomplete="off" required>
            <button type="submit">전송</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function () {
    // 채팅창이 로드되면 스크롤을 항상 가장 아래로 내립니다.
    var chatWindow = $('#message-list');
    if (chatWindow.length > 0) {
        chatWindow.scrollTop(chatWindow[0].scrollHeight);
    }

    // 채팅 입력 폼이 제출되면 실행됩니다.
    $('#chat-form').on('submit', function (e) {
        e.preventDefault(); // 페이지 새로고침을 막습니다.
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(), // 폼 데이터를 전송합니다.
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // 메시지 전송에 성공하면, 채팅창 전체를 새로 로드하여 새 메시지를 반영합니다.
                    $('#chat-overlay').load('chat.php');
                } else {
                    alert('메시지 전송 실패: ' + response.message);
                }
            }
        });
    });

    <?php if ($is_admin): // 관리자 전용 삭제 기능입니다. ?>
    let pressTimer;
    // 메시지 영역에 마우스를 누르거나 터치를 시작하면 타이머를 설정합니다.
    $('#chat-overlay').on('mousedown touchstart', '.message-row', function (e) {
        let messageRow = $(this);
        // 0.8초(800ms) 후에 안의 함수를 실행하는 타이머를 설정합니다.
        pressTimer = window.setTimeout(function () {
            if (confirm('이 메시지를 삭제하시겠습니까?')) {
                // 'data-id'에 저장해 둔 메시지 ID를 가져옵니다.
                const messageId = messageRow.data('id');
                $.ajax({
                    url: 'ajax_delete_chat.php',
                    type: 'POST',
                    data: { id: messageId },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // 삭제 성공 시, 메시지가 서서히 사라지는 효과를 주고 DOM에서 제거합니다.
                            messageRow.fadeOut(300, function () { $(this).remove(); });
                        } else {
                            alert('삭제 실패: ' + response.message);
                        }
                    }
                });
            }
        }, 800);
    // 마우스 버튼을 떼거나, 영역을 벗어나거나, 터치가 끝나면 타이머를 취소합니다.
    }).on('mouseup mouseleave touchend', '.message-row', function () { 
        clearTimeout(pressTimer);
    });
    <?php endif; ?>
});
</script>