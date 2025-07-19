<?php
// --- 파일 경로: /pages/gallery.php ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$posts = [];

// DB에서 'gallery' 타입의 게시물을 최신순으로 가져옵니다.
$sql = "SELECT id, title, thumbnail_path FROM posts WHERE type = 'gallery' ORDER BY id DESC";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $result->free();
}
?>

<div class="gallery-container">
    <div class="gallery-header">
        <h2>Gallery</h2>
        <?php if ($is_admin): ?>
            <a href="#/gallery_upload" class="add-btn">추가하기</a>
        <?php endif; ?>
    </div>

    <div class="gallery-grid">
        <?php if (empty($posts)): ?>
            <p>아직 게시물이 없습니다.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                    <?php
                        // 썸네일 경로가 있으면 style 속성에 배경 이미지 추가
                        $thumbnail_style = !empty($post['thumbnail_path']) ? 'background-image: url(\'' . htmlspecialchars($post['thumbnail_path']) . '\');' : '';
                    ?>
                    <div class="item-thumbnail" style="<?php echo $thumbnail_style; ?>"></div>
                    <div class="gallery-item-title"><?php echo htmlspecialchars($post['title']); ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>