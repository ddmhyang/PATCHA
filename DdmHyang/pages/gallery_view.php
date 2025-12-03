<?php
require_once '../includes/db.php';

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물 ID입니다."); }

$stmt = $mysqli->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 존재하지 않습니다."); }

$can_view = false;
if ($post['is_private'] == 0 || $is_admin || (isset($_SESSION['post_access'][$post_id]) && (time() - $_SESSION['post_access'][$post_id] < 1800))) {
    $can_view = true;
} else {
    unset($_SESSION['post_access'][$post_id]);
}

if (!$can_view) {
    include 'gallery_password.php';
    exit;
}
?>
<div class="page-container" id="main_content">
    <div class="main-frame view-mode">
        <div class="view-container">
            <div class="view-header">
                <span class="gallery-badge">
                    <?php echo htmlspecialchars($post['gallery_type']); ?>
                </span>
                
                <h1 class="view-title">
                    <?php echo htmlspecialchars($post['title']);?>
                </h1>

                <div class="view-meta">
                    작성일 : <?php echo date("Y.m.d H:i", strtotime($post['created_at'])); ?>
                </div>

                <?php if ($is_admin): ?>
                    <div class="admin-controls">
                        <a href="#/gallery_edit?id=<?php echo $post_id; ?>" class="action-btn btn-sm">
                            <i class="fa-solid fa-wrench"></i> 수정
                        </a>
                        <button class="action-btn btn-sm delete-btn" 
                                data-id="<?php echo $post_id; ?>" 
                                data-type="<?php echo $post['gallery_type']; ?>">
                            <i class="fa-solid fa-trash"></i> 삭제
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="view-body">
                <?php echo $post['content']; ?>
            </div>

            <div class="view-footer">
                <a href="#/<?php echo htmlspecialchars($post['gallery_type']); ?>" class="action-btn btn-lg">
                    <i class="fa-solid fa-list"></i> 목록으로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>