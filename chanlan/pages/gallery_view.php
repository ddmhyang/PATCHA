<?php
require_once '../includes/db.php';

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물입니다."); }

$stmt = $mysqli->prepare("SELECT * FROM home_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 존재하지 않습니다."); }

// --- 비밀글 접근 제어 로직 ---
$can_view = false;
if ($post['is_private'] == 0) { // 공개글
    $can_view = true;
} elseif ($is_admin) { // 관리자
    $can_view = true;
} else { // 비밀글 + 일반 사용자
    // 세션에 저장된 접근 시간 확인
    if (isset($_SESSION['post_access'][$post_id])) {
        $access_time = $_SESSION['post_access'][$post_id];
        // 30분(1800초)이 지났는지 확인
        if (time() - $access_time < 1800) {
            $can_view = true;
        } else {
            // 30분 초과 시 세션 정보 삭제
            unset($_SESSION['post_access'][$post_id]);
        }
    }
}

if (!$can_view) {
    // 비밀번호 입력 폼으로 리디렉션 대신, 해당 파일을 include
    include 'gallery_password.php';
    exit; // 비밀번호 폼을 보여주고 여기서 실행 종료
}
// --- 접근 제어 로직 끝 ---
?>

<div class="view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?> 🔒</h1>
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