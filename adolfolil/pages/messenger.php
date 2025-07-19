<?php
// --- 파일 경로: /pages/messenger.php (카카오톡 방식 최종 수정본) ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// 메시지를 작성된 시간 순서대로 가져옵니다.
$messages = $mysqli->query("SELECT * FROM messages ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="chat-container">
    <div class="chat-header">> DolfoLil Messenger</div>
    
    <div class="chat-window" id="message-list">
        <?php foreach ($messages as $msg): ?>
            <div class="message-row <?php echo ($msg['character_name'] === 'Lilian') ? 'received' : 'sent'; ?>" 
                 data-id="<?php echo $msg['id']; ?>"
                 data-is-admin="<?php echo $is_admin ? 'true' : 'false'; ?>">
                
                <img class="profile-pic" src="../assets/images/<?php echo ($msg['character_name'] === 'Lilian') ? 'torken2.png' : 'torken.png'; ?>">
                
                <div class="message-bubble">
                    <div class="character-name"><?php echo htmlspecialchars($msg['character_name']); ?></div>
                    <p class="message-text"><?php echo htmlspecialchars($msg['message_text']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="chat-input-area">
        <form id="messenger-form" action="../actions/messenger_save.php" method="post">
            <select name="character">
                <option value="Adolfo">Adolfo</option>
                <option value="Lilian">Lilian</option>
            </select>
            <input type="text" name="message" placeholder="메시지를 입력하세요..." autocomplete="off" required>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit">전송</button>
        </form>
    </div>
</div>

<style>
    .chat-container { width: 458px; height: 638px; background: #1A1A1A; display: flex; flex-direction: column; }
    .chat-header { padding: 15px; color: white; font-family: 'DungGeunMo'; font-size: 24px; }
    .chat-window { flex-grow: 1; background: #b2c7d9; overflow-y: auto; padding: 10px; }
    .message-row { display: flex; margin-bottom: 15px; max-width: 70%; align-items: flex-start; }
    .message-row.sent { margin-left: auto; flex-direction: row-reverse; } /* 보낸 메시지: 오른쪽 */
    .message-row.received { margin-right: auto; } /* 받은 메시지: 왼쪽 */
    .profile-pic { width: 40px; height: 40px; border-radius: 15px; margin: 0 8px; }
    .message-bubble { background: white; border-radius: 10px; padding: 8px 12px; }
    .message-row.sent .message-bubble { background: #ffeb33; }
    .character-name { font-size: 14px; font-weight: bold; margin-bottom: 4px; }
    .message-text { margin: 0; font-size: 16px; }
    .chat-input-area form { display: flex; height: 60px; }
    .chat-input-area select, .chat-input-area input, .chat-input-area button { border: none; padding: 10px; }
    .chat-input-area input { flex-grow: 1; }
</style>