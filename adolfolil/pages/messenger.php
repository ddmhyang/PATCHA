<?php
// --- 파일 경로: /pages/messenger.php (카카오톡 방식 최종 수정본) ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (!$is_admin) {
    echo "<script>
            alert('로그인이 필요합니다.');
            window.location.href = 'main.php';
          </script>";
    exit;
}

$messages = $mysqli->query("SELECT * FROM messages ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="chat-container">
    <div class="chat-header">> DolfoLil</div>

    <div class="chat-window" id="message-list">
        <?php foreach ($messages as $msg): ?>
        <div
            class="message-row <?php echo ($msg['character_name'] === 'Adolfo') ? 'received' : 'sent'; ?>"
            data-id="<?php echo $msg['id']; ?>"
            data-is-admin="<?php echo $is_admin ? 'true' : 'false'; ?>">

            <img
                class="profile-pic"
                src="../assets/img/<?php echo ($msg['character_name'] === 'Adolfo') ? 'torken.png' : 'torken2.png'; ?>">

            <div class="message-bubble">
                <div class="character-name"><?php echo htmlspecialchars($msg['character_name']); ?></div>
                <p class="message-text"><?php echo htmlspecialchars($msg['message_text']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="chat-input-area">
        <form id="messenger-form" action="../actions/messenger_save.php" method="post">
            <select style="font-family: 'Galmuri9';" name="character">
                <option style="font-family: 'Galmuri9';" value="Adolfo">Adolfo</option>
                <option style="font-family: 'Galmuri9';" value="Lilian">Lilian</option>
            </select>
            <input
                type="text"
                name="message"
                placeholder="메시지를 입력하세요..."
                autocomplete="off"
                required="required"
                style="font-family: 'Galmuri9';">
            <input
                type="hidden"
                name="csrf_token"
                style="font-family: 'Galmuri9';"
                value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit">전송</button>
        </form>
    </div>
</div>

<style>
    .chat-container {
        width: 458px;
        height: 638px;
        background: #1A1A1A;
        font-family: 'Galmuri9';
        display: flex;
        flex-direction: column;
    }
    .chat-header {
        padding: 31px 0 18px 25px;
        color: #fafafa;
        font-family: 'DungGeunMo';
        font-size: 24px;
    }
    .chat-window {
        position: absolute;
        left: 50%;
        bottom: 81px;
        transform: translateX(-50%);
        flex-grow: 1;
        width: 422px;
        height: 467px;
        flex-shrink: 0;
        background: linear-gradient(0deg, rgba(250, 250, 250, 0.50) 0%, rgba(250, 250, 250, 0.50) 100%), linear-gradient(0deg, rgba(26, 26, 26, 0.50) 0%, rgba(26, 26, 26, 0.50) 100%), url("../assets/img/logo1.jpg") lightgray -44px 0px / 117.264% 100% no-repeat;
        overflow-y: auto;
        padding-bottom: 20px;
    }
    .chat-window::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
    .message-row {
        display: flex;
        margin-top: 20px;
        max-width: 70%;
        align-items: flex-start;
    }
    .message-row.sent {
        margin-left: auto;
        flex-direction: row-reverse;
    }
    /* 보낸 메시지: 오른쪽 */
    .message-row.received {
        margin-right: auto;
    }
    /* 받은 메시지: 왼쪽 */
    .profile-pic {
        width: 64px;
        height: 64px;
        border-radius: 64px;
        margin: 0 13px;
        border: 2px solid #1A1A1A;
    }
    .message-bubble {
        background: aqua;
        border-radius: 10px;
        padding: 8px 12px;
    }
    .message-row.sent .message-bubble {
        color: #1a1a1a;
        text-align: right;
        min-width: 120px;
        background: #fafafa;
        min-height: 50px;
    }
    .message-row.received .message-bubble {
        min-width: 120px;
        color: #fafafa;
        background: #1a1a1a;
        min-height: 50px;
    }
    .character-name {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 4px;
    }

    .message-row.sent .character-name {
        text-align: right;
    }
    .message-text {
        margin: 0;
        font-size: 16px;
    }
    .chat-input-area form {
        position: absolute;
        left: 50%;
        bottom: 21px;
        transform: translateX(-50%);
        display: flex;
        height: 60px;
        width: 422px;
    }
    .chat-input-area button,
    .chat-input-area input,
    .chat-input-area select{
        border: none;
        padding: 20px;
    }
    .chat-input-area input {
        flex-grow: 1;
    }
</style>

<script>
    $(document).ready(function () {
        var chatWindow = $('#message-list');
        chatWindow.scrollTop(chatWindow[0].scrollHeight);
    });
</script>