<?php
require_once '../includes/db.php';
$gallery_type = 'trpg';
// 타임라인은 시간 순서대로 보여야 하므로, ORDER BY created_at ASC 로 변경
$posts = $mysqli->query("SELECT id, title, thumbnail, is_private FROM chan_gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="timeline-container">
    <?php if ($is_admin): ?><a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="add-btn">새 글 작성</a><?php endif; ?>
    <div class="timeline-wrapper">
        <div class="timeline-events">
            <div class="timeline-line"></div>
            <?php foreach ($posts as $index => $post): ?>
                <?php
                    $position_class = ($index % 2 == 0) ? 'top' : 'bottom';
                    
                    $thumbnail_url = $post['thumbnail'] ?? '';
                    $style = !empty($thumbnail_url) 
                        ? "background-image: url('" . htmlspecialchars($thumbnail_url) . "');" 
                        : ""; // 썸네일 없으면 CSS 기본 회색 배경 사용
                ?>
                <div class="timeline-item <?php echo $position_class; ?>">
                    <a href="#/gallery_view?id=<?php echo $post['id']; ?>">
                        <div class="item-thumbnail" style="<?php echo $style; ?>"></div>
                        <div class="item-text">
                            <h3><?php echo htmlspecialchars($post['title']);?></h3>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>