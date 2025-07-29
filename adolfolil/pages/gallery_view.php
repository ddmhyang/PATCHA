<?php

require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (!isset($_GET['id'])) { exit("잘못된 접근입니다."); }
$post_id = intval($_GET['id']);

$stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ? AND type = 'gallery'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) { exit("게시물이 존재하지 않습니다."); }
?>

<div class="view-container">
    <div class="gallery-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if ($is_admin): ?>
            <div class="admin-buttons">
                <a href="#/gallery_edit?id=<?php echo $post_id; ?>" class="btn btn-secondary">수정</a>
                <button class="delete-btn btn btn-primary" data-id="<?php echo $post_id; ?>" data-type="gallery">삭제</button>
            </div>
        <?php endif; ?>
    </div>

    <div class="view-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>

    <div class="view-content">
        <?php echo $post['content']; ?>
    </div>
    
    <div class="view-footer">
        <a href="#/gallery" class="btn btn-secondary">목록으로</a>
    </div>
</div>