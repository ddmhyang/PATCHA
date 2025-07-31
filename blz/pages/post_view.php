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

if (!isset($_GET['id'])) {
    echo "잘못된 접근입니다.";
    exit;
}
$post_id = intval($_GET['id']);

$stmt = $mysqli->prepare("SELECT * FROM blz_posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "게시물이 존재하지 않습니다.";
    exit;
}
?>

<div class="post-view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">
        <?php if ($is_admin): ?>
            <a href="#/post_edit?id=<?php echo $post['id']; ?>" class="btn-action">수정</a>
            <button class="btn-action btn-delete delete-post-btn" data-id="<?php echo $post['id']; ?>" data-type="<?php echo $post['type']; ?>">삭제</button>
        <?php endif; ?>
    </div>
    <hr>
    <div class="post-date">작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></div>
    <div class="post-content">
        <?php echo $post['content']; ?>
        <div class="post-actions">
            <a href="#/<?php echo htmlspecialchars($post['type']); ?>" class="btn-back-to-list">목록으로</a>
        </div>
    </div>

</div>