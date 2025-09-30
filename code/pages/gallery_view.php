<?php
require_once '../includes/db.php';

// URL 파라미터에서 게시물 ID를 가져옵니다.
$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물 ID입니다."); }

// 해당 ID의 게시물 정보를 DB에서 가져옵니다.
$stmt = $mysqli->prepare("SELECT * FROM chan_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 존재하지 않습니다."); }

// 게시물을 볼 수 있는지 권한을 체크하는 변수. 기본값은 false.
$can_view = false;
// 1. 게시물이 비밀글이 아니거나 (is_private == 0)
// 2. 현재 사용자가 관리자이거나
// 3. 세션에 이 게시물에 대한 접근 허가 정보가 있고, 허가받은 지 30분(1800초)이 지나지 않았다면
if ($post['is_private'] == 0 || $is_admin || (isset($_SESSION['post_access'][$post_id]) && (time() - $_SESSION['post_access'][$post_id] < 1800))) {
    $can_view = true; // 볼 수 있는 권한을 true로 변경
} else {
    // 30분이 지났다면, 접근 허가 정보를 세션에서 삭제하여 다시 비밀번호를 묻도록 합니다.
    unset($_SESSION['post_access'][$post_id]);
}

// 만약 최종적으로 볼 수 있는 권한이 없다면,
if (!$can_view) {
    // 비밀번호 입력 폼 페이지를 포함시키고,
    include 'gallery_password.php';
    // 현재 스크립트 실행을 여기서 중단합니다. (아래 내용이 실행되지 않음)
    exit;
}
?>
<div class="view-container">
    <h1><?php echo htmlspecialchars($post['title']);?></h1>
    
    <div class="post-meta">
        <?php if ($is_admin): ?>
            <a href="#/gallery_edit?id=<?php echo $post_id; ?>" class="btn-action">수정</a>
            <button class="btn-action delete-btn" data-id="<?php echo $post_id; ?>" data-type="<?php echo $post['gallery_type']; ?>">삭제</button>
        <?php endif; ?>
    </div>
    <hr>
    <div class="post-date">작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></div>
    
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    
    <div class="post-actions">
        <a href="#/<?php echo htmlspecialchars($post['gallery_type']); ?>" class="btn-back-to-list">목록으로</a>
    </div>
</div>