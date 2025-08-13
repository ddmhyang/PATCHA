<?php
require_once '../includes/db.php';
$gallery_type = 'gallery'; // trpg.php에서는 'trpg'로 변경
$posts = $mysqli->query("SELECT id, title, thumbnail, is_private FROM chan_gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="gallery-container">
    <h2><?php echo ucfirst($gallery_type); ?></h2>
    <?php if ($is_admin): ?><a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="add-btn">새 글 작성</a><?php endif; ?>
    <div class="gallery-grid">
        <?php foreach ($posts as $post): ?>
            <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                <?php
                    $thumbnail_url = $post['thumbnail'] ?? '';
                    // 썸네일이 없으면 회색 배경, 있으면 이미지 배경
                    $style = !empty($thumbnail_url) 
                        ? "background-image: url('" . htmlspecialchars($thumbnail_url) . "');" 
                        : "background-color: #555;"; // 회색 배경
                ?>
                <div class="item-thumbnail" style="<?php echo $style; ?>"></div>
                <h3><?php echo htmlspecialchars($post['title']); if ($post['is_private']) echo ' 🔒'; ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</div>