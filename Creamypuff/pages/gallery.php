<?php
require_once '../includes/db.php';
// 이 페이지는 'gallery' 타입의 게시물만 다룹니다.
$gallery_type = 'gallery';
// 'gallery' 테이블에서 gallery_type이 'gallery'인 게시물들을 최신순(created_at DESC)으로 가져옵니다.
// fetch_all(MYSQLI_ASSOC)은 결과를 모두 가져와 연관 배열의 배열로 만듭니다.
$posts = $mysqli->query("SELECT id, title, thumbnail, is_private FROM gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="gallery-container">
    <h2><?php echo ucfirst($gallery_type); ?></h2>
    <?php if ($is_admin): ?><a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="add-btn">새 글 작성</a><?php endif; ?>
    <div class="gallery-grid">
        <?php foreach ($posts as $post): ?>
            <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                <?php
                    // 썸네일 URL이 있는지 확인합니다.
                    $thumbnail_url = $post['thumbnail'] ?? '';
                    // 썸네일 URL이 있으면 배경 이미지 스타일을, 없으면 기본 배경색 스타일을 적용합니다.
                    $style = !empty($thumbnail_url) 
                        ? "background-image: url('" . htmlspecialchars($thumbnail_url) . "');" 
                        : "background-color: #7078A750;";
                ?>
                <div class="item-thumbnail" style="<?php echo $style; ?>"></div>
                <h3><?php echo htmlspecialchars($post['title']);?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</div>