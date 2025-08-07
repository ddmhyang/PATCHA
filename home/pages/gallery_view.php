<?php
require_once '../includes/db.php';

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물입니다."); }

$stmt = $mysqli->prepare("SELECT * FROM home_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 존재하지 않습니다."); }
?>
<div class="view-container">
    <div class="view-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if ($is_admin): ?>
            <div class="admin-buttons">
                <a href="#/gallery_edit?id=<?php echo $post_id; ?>" class="btn">수정</a>
                <button class="btn delete-btn" data-id="<?php echo $post_id; ?>" data-type="<?php echo $post['gallery_type']; ?>">삭제</button>
            </div>
        <?php endif; ?>
    </div>
    <div class="view-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>
    <div class="view-content">
        <?php echo $post['content']; ?>
    </div>
    <div class="view-footer">
        <a href="#/<?php echo htmlspecialchars($post['gallery_type']); ?>" class="btn">목록으로</a>
    </div>
</div>