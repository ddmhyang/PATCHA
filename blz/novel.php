<?php
// /blz/art.php (수정된 코드)
require_once 'includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$gallery_type = 'novel'; // novel.php 에서는 'novel'

$posts = $mysqli->query("SELECT id, title, thumbnail_path FROM blz_posts WHERE type = '$gallery_type' ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="gallery-container">
    <div class="page-header">
        <div class="page-title">Novel</div>
        <div class="page-divider"></div>
    </div>
    <?php if ($is_admin): ?>
        <a href="#/post_upload?type=<?php echo $gallery_type; ?>" class="add-post-btn">새 글 작성</a>
    <?php endif; ?>
    <div class="gallery-grid">
        <?php foreach ($posts as $post): ?>
            <a href="#/post_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                <?php
                // DB에 저장된 썸네일 경로가 있으면 사용, 없으면 빈 값으로 두고 CSS로 처리
                $thumbnail_url = !empty($post['thumbnail_path']) ? htmlspecialchars($post['thumbnail_path']) : '';
                ?>
                <div class="thumbnail" style="background-image: url('<?php echo $thumbnail_url; ?>');"></div>
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</div>