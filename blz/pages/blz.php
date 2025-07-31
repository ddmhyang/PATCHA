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

$blue_slug = 'blz_blue_intro';
$stmt_blue = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt_blue->bind_param("s", $blue_slug); $stmt_blue->execute();
$blue_content = $stmt_blue->get_result()->fetch_assoc()['content'] ?? '<h2>Blue</h2><p>내용을 입력하세요.</p>';
$stmt_blue->close();

$owen_slug = 'blz_owen_intro';
$stmt_owen = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt_owen->bind_param("s", $owen_slug); $stmt_owen->execute();
$owen_content = $stmt_owen->get_result()->fetch_assoc()['content'] ?? '<h2>Owen</h2><p>내용을 입력하세요.</p>';
$stmt_owen->close();
?>

<div class="blz-page-wrapper">
    <div class="blz-notes-container">
        <div class="blz-note-item page-content" data-slug="<?php echo $blue_slug; ?>">
            <div class="view-mode">
                <a href="#/blue" class="blz-note-item-link">
                    <div class="content-display"><?php echo $blue_content; ?></div>
                </a>
                <?php if ($is_admin): ?>
                    <div class="button-wrapper">
                        <button type="button" class="edit-btn">수정</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="edit-mode" style="display: none;">
                <form class="content-form">
                    <textarea class="summernote"><?php echo htmlspecialchars($blue_content); ?></textarea>
                    <div class="button-wrapper">
                        <button type="button" class="save-btn">저장</button>
                        <button type="button" class="cancel-btn">취소</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="blz-note-item page-content" data-slug="<?php echo $owen_slug; ?>">
            <div class="view-mode">
                <a href="#/owen" class="blz-note-item-link">
                    <div class="content-display"><?php echo $owen_content; ?></div>
                </a>
                <?php if ($is_admin): ?>
                    <div class="button-wrapper">
                        <button type="button" class="edit-btn">수정</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="edit-mode" style="display: none;">
                <form class="content-form">
                    <textarea class="summernote"><?php echo htmlspecialchars($owen_content); ?></textarea>
                    <div class="button-wrapper">
                        <button type="button" class="save-btn">저장</button>
                        <button type="button" class="cancel-btn">취소</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>