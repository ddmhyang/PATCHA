<?php
session_start();
if (!isset($_SESSION['blz_logged_in']) || $_SESSION['blz_logged_in'] !== true) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        die('권한이 없습니다.');
    } else {
        header('Location: ../index.php');
    }
    exit;
}
?>
<?php
require_once '../includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$gallery_type = 'novel';

$posts = $mysqli->query("SELECT id, title, thumbnail_path FROM blz_posts WHERE type = '$gallery_type' ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="gallery-page-wrapper">
    <div class="gallery-container">
        <?php if ($is_admin): ?>
            <a href="#/post_upload?type=<?php echo $gallery_type; ?>" class="add-post-btn">새 글 작성</a>
        <?php endif; ?>
        
        <div class="gallery-grid">
            <?php if (empty($posts)): ?>
                <p class="no-posts">게시물이 없습니다.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <a href="#/post_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                        <?php
                        $thumbnail_style = !empty($post['thumbnail_path']) 
                            ? "background-image: url('" . htmlspecialchars($post['thumbnail_path']) . "');" 
                            : "";
                        ?>
                        <div class="thumbnail" style="<?php echo $thumbnail_style; ?>"></div>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>