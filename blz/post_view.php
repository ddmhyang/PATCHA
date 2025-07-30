<?php
// /blz/post_view.php
require_once 'includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

if (!isset($_GET['id'])) {
    echo "잘못된 접근입니다.";
    exit;
}
$post_id = intval($_GET['id']);

$stmt = $mysqli->prepare("SELECT * FROM blz_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "게시물이 존재하지 않습니다.";
    exit;
}
?>

<div class="post-view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
        <?php if ($is_admin): ?>
            <div class="admin-actions">
                <a href="#/post_edit?id=<?php echo $post['id']; ?>">수정</a>
                <button class="delete-post-btn" data-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['type']; ?>">삭제</button>
            </div>
        <?php endif; ?>
    </div>
    <hr>
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    <a href="#/<?php echo htmlspecialchars($post['type']); ?>" class="back-to-list">목록으로</a>
</div>