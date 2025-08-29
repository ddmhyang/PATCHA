<?php
require_once 'includes/db.php';
if (!isset($_GET['id']) || !($post_id = intval($_GET['id']))) {
    die("유효하지 않은 게시물입니다.");
}

$stmt = $mysqli->prepare("SELECT * FROM home2_timeline WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    die("게시물이 존재하지 않습니다.");
}
?>
<div class="view-container">
    <div class="view-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if ($is_admin): ?>
            <div class="admin-buttons">
                <a href="#/timeline_form?id=<?php echo $post_id; ?>" class="btn-edit">수정</a>
                <button class="btn-delete delete-btn" data-id="<?php echo $post_id; ?>">삭제</button>
            </div>
        <?php endif; ?>
    </div>

    <div class="view-meta">
        <?php if (!empty($post['chapter'])): ?>
            <span class="chapter-tag"><?php echo htmlspecialchars($post['chapter']); ?></span>
        <?php endif; ?>
        <span class="date-tag">작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>

    <div class="view-content">
        <?php echo $post['content']; ?>
    </div>
    <a href="#/timeline" class="btn-back">목록으로</a>
</div>