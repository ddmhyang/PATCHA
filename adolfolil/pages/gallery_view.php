<?php
// --- 파일 경로: /pages/gallery_view.php (최종 수정본) ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (!isset($_GET['id'])) { exit("잘못된 접근입니다."); }
$post_id = intval($_GET['id']);

// DB에서 id와 type='gallery'로 게시물을 찾습니다.
$stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ? AND type = 'gallery'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) { exit("게시물이 존재하지 않습니다."); }
?>

<div class="view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="view-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>

    <div class="view-content">
        <?php echo $post['content']; // 썸네일 출력 코드를 완전히 제거하고 본문 내용만 출력합니다. ?>
    </div>
    
    <?php if ($is_admin): ?>
    <div class="admin-buttons">
        <a href="#/gallery_edit?id=<?php echo $post_id; ?>">수정</a>
        <button class="delete-btn" data-id="<?php echo $post_id; ?>" data-type="gallery">삭제</button>
    </div>
    <?php endif; ?>
    <a href="#/gallery" class="btn-back-to-list">목록으로</a>
</div>