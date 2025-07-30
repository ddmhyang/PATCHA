<?php
// /blz/owen.php (수정된 코드)
require_once 'includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$page_slug = 'owen';

$stmt = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다. 관리자 모드에서 수정해주세요.</p>';
$stmt->close();
?>

<div class="page-header">
    <div class="page-title">Owen</div>
    <div class="page-divider"></div>
</div>

<div class="page-content" data-slug="<?php echo $page_slug; ?>">
    <div class="view-mode">
        <div class="content-display"><?php echo $page_content; ?>
            <?php if ($is_admin): ?>
                <div class="button-wrapper">
                    <button type="button" class="edit-btn">수정하기</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="edit-mode" style="display: none;">
        <form class="content-form">
            <textarea class="summernote"><?php echo htmlspecialchars($page_content); ?></textarea>
            <div class="button-wrapper">
                <button type="button" class="save-btn">저장</button>
                <button type="button" class="cancel-btn">취소</button>
            </div>
        </form>
    </div>
</div>