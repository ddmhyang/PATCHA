<?php
require_once 'includes/db.php';

// sHotel의 'gallery' 테이블에서 데이터를 가져옵니다.
$posts_query = "SELECT id, title, thumbnail FROM gallery ORDER BY created_at DESC";
$posts_result = $mysqli->query($posts_query);
$posts = [];
if ($posts_result->num_rows > 0) {
    while($row = $posts_result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>

<div class="gallery-container">
    <h2>Gallery</h2>
    <?php // 로그인 상태일 때만 '게시글 추가하기' 버튼 표시
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <a href="index.php?page=gallery_upload" class="add-btn">게시글 추가하기</a>
    <?php endif; ?>
    
    <div class="gallery-grid">
        <?php foreach ($posts as $post): ?>
            <a href="index.php?page=gallery_view&id=<?php echo $post['id']; ?>" class="gallery-item">
                <?php
                    // chanlan과 동일한 썸네일 표시 로직
                    $thumbnail_url = $post['thumbnail'] ?? '';
                    $style = !empty($thumbnail_url) 
                        ? "background-image: url('" . htmlspecialchars($thumbnail_url) . "');" 
                        : "background-color: #7078A750;"; // 썸네일이 없을 경우 기본 배경색
                ?>
                <div class="item-thumbnail" style="<?php echo $style; ?>"></div>
                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
            </a>
        <?php endforeach; ?>
    </div>
</div>