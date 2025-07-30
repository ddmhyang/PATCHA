<?php
// /blz/main.php (수정된 코드)
require_once 'includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$page_slug = 'main_content'; // blue.php는 'blue', owen.php는 'owen'

$stmt = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다. 관리자 모드에서 수정해주세요.</p>';
$stmt->close();
?>

<div class="page-header">
    <div class="page-title">Main</div>
    <div class="page-divider"></div>
</div>

<div class="page-content" data-slug="<?php echo $page_slug; ?>">
    <!-- 보기 모드 -->
    <div class="view-mode">
        <div class="content-display"><?php echo $page_content; ?></div>
        <?php if ($is_admin): ?>
            <button type="button" class="edit-btn">수정하기</button>
        <?php endif; ?>
    </div>

    <!-- 수정 모드 (숨겨져 있음) -->
    <div class="edit-mode" style="display: none;">
        <form class="content-form">
            <textarea class="summernote"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="button" class="save-btn">저장</button>
            <button type="button" class="cancel-btn">취소</button>
        </form>
    </div>
</div>