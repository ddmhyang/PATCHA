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
$page_slug = 'main_content';

$stmt = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$page_content = $stmt->get_result()->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다.</p>';
$stmt->close();
?>

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