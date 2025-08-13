<?php
require_once '../includes/db.php';

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물 ID입니다."); }

$stmt = $mysqli->prepare("SELECT * FROM chan_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 존재하지 않습니다."); }

// --- 비밀글 접근 제어 로직 수정 ---
$can_view = false;
if ($post['is_private'] == 0) { // 1. 공개글이면 통과
    $can_view = true;
} elseif ($is_admin) { // 2. 관리자면 통과
    $can_view = true;
} else { // 3. 비밀글일 경우 세션 확인
    if (isset($_SESSION['post_access'][$post_id]) && (time() - $_SESSION['post_access'][$post_id] < 1800)) {
        $can_view = true;
    } else {
        unset($_SESSION['post_access'][$post_id]); // 시간 만료 시 세션 제거
    }
}

if (!$can_view) {
    include 'gallery_password.php'; // 비밀번호 입력 폼 표시
    exit;
}
?>
<div class="view-container">
    <h1><?php echo htmlspecialchars($post['title']); if ($post['is_private']) echo ' 🔒'; ?></h1>
    <?php if ($is_admin): ?>
        <a href="#/gallery_edit?id=<?php echo $post_id; ?>">수정</a>
        <button class="delete-btn" data-id="<?php echo $post_id; ?>" data-type="<?php echo $post['gallery_type']; ?>">삭제</button>
    <?php endif; ?>
    <div class="view-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>
    <div class="view-content">
        <?php echo $post['content']; ?>
    </div>
    <a href="#/<?php echo htmlspecialchars($post['gallery_type']); ?>">목록으로</a>
</div>