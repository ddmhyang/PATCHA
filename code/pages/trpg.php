<?php
require_once '../includes/db.php';
// 이 페이지는 'trpg' 타입의 게시물만 다룹니다.
$gallery_type = 'trpg';
// 'chan_gallery' 테이블에서 gallery_type이 'trpg'인 게시물들을 작성된 순서(created_at ASC)대로 가져옵니다.
$posts = $mysqli->query("SELECT id, title, thumbnail, is_private FROM chan_gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="timeline-container">
    <?php if ($is_admin): ?><a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="add-btn">새 글 작성</a><?php endif; ?>
    <div class="timeline-wrapper">
        <div class="timeline-events">
            <div class="timeline-line"></div>
            <?php foreach ($posts as $index => $post): ?>
                <?php
                    // $index가 짝수이면 'top', 홀수이면 'bottom' 클래스를 주어 타임라인 위아래로 번갈아 배치합니다.
                    $position_class = ($index % 2 == 0) ? 'top' : 'bottom';
                    
                    // 썸네일이 있으면 배경 이미지로, 없으면 아무 스타일도 주지 않습니다. (gallery.php와 약간 다름)
                    $thumbnail_url = $post['thumbnail'] ?? '';
                    $style = !empty($thumbnail_url) 
                        ? "background-image: url('" . htmlspecialchars($thumbnail_url) . "');" 
                        : ""; 
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