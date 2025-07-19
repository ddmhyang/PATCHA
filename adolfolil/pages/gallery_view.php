<?php
// --- 파일 경로: /pages/gallery_view.php (최종 수정본) ---
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

// ▼▼▼ 본문 내용을 별도의 변수에 저장 ▼▼▼
$display_content = $post['content'];

// 썸네일이 존재하고, 그 썸네일이 본문의 첫 이미지와 동일하다면 본문에서 해당 이미지를 제거합니다.
if (!empty($post['thumbnail_path'])) {
    preg_match('/<img[^>]+src="([^">]+)"/', $post['content'], $matches);
    if (isset($matches[1]) && $matches[1] === $post['thumbnail_path']) {
        // 이미지를 감싸는 <p> 태그까지 포함하여 한 번만 교체합니다.
        $display_content = preg_replace('/<p>\s*<img[^>]+src="'.preg_quote($matches[1], '/').'"[^>]*>\s*<\/p>/', '', $post['content'], 1);
    }
}
?>

<div class="view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="view-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>

    <?php if (!empty($post['thumbnail_path'])): ?>
        <img class="view-thumbnail" src="<?php echo htmlspecialchars($post['thumbnail_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
    <?php endif; ?>

    <div class="view-content">
        <?php echo $display_content; ?>
    </div>
    
    <?php if ($is_admin): ?>
    <div class="admin-buttons">
        <a href="#/gallery_edit?id=<?php echo $post_id; ?>">수정</a>
        <button class="delete-btn" data-id="<?php echo $post_id; ?>" data-type="gallery">삭제</button>
    </div>
    <?php endif; ?>
    <a href="#/gallery" class="btn-back-to-list">목록으로</a>
</div>