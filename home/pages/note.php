<?php
require_once '../includes/db.php';
$notes = $mysqli->query("SELECT * FROM home_note ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="note-container">
    <form class="ajax-form" action="ajax_save_note.php" method="post">
        <input type="text" name="writer" placeholder="작성자" required>
        <textarea name="content" placeholder="내용을 입력하세요..." required></textarea>
        <button type="submit">남기기</button>
    </form>
    <div class="note-list">
        <?php foreach ($notes as $note): ?>
        <div class="note-item">
            <p><strong><?php echo htmlspecialchars($note['writer']); ?></strong> <span class="note-date">(<?php echo $note['created_at']; ?>)</span></p>
            <p><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>