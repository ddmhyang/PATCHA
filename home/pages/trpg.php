<?php
require_once '../includes/db.php';
$gallery_type = 'trpg';

$posts = $mysqli->query("SELECT id, title, thumbnail FROM home_gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="gallery-container">
    <div class="gallery-header">
        <h2><?php echo ucfirst($gallery_type); ?></h2>
        <?php if ($is_admin): ?>
            <a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="add-btn">새 글 작성</a>
        <?php endif; ?>
    </div>
    <div class="gallery-grid">
        <?php foreach ($posts as $post): ?>
            <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                <div class.item-thumbnail" style="background-image: url('<?php echo htmlspecialchars($post['thumbnail'] ?? '../assets/images/placeholder.png'); ?>');"></div>
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</div>