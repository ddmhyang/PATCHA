<?php
// /blz/blz.php (최종 수정본)
require_once 'includes/db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Blue, Owen 소개 콘텐츠 불러오기
$blue_slug = 'blz_blue_intro';
$stmt_blue = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt_blue->bind_param("s", $blue_slug); $stmt_blue->execute();
$blue_content = $stmt_blue->get_result()->fetch_assoc()['content'] ?? '<h2><a href="#/blue">Blue</a></h2><p>내용을 입력하세요.</p>';
$stmt_blue->close();

$owen_slug = 'blz_owen_intro';
$stmt_owen = $mysqli->prepare("SELECT content FROM blz_pages WHERE slug = ?");
$stmt_owen->bind_param("s", $owen_slug); $stmt_owen->execute();
$owen_content = $stmt_owen->get_result()->fetch_assoc()['content'] ?? '<h2><a href="#/owen">Owen</a></h2><p>내용을 입력하세요.</p>';
$stmt_owen->close();
?>

<div class="blz-page-wrapper">
    <div class="page-header">
        <div class="page-title">BlOw</div>
        <div class="page-divider"></div>
    </div>

    <div class="blz-notes-container">
        <div class="blz-note-item page-content" data-slug="<?php echo $blue_slug; ?>">
            <div class="view-mode">
                <div class="content-display"><?php echo $blue_content; ?></div>
                <?php if ($is_admin): ?><button type="button" class="edit-btn">수정</button><?php endif; ?>
            </div>
            <div class="edit-mode" style="display: none;">
                <form class="content-form">
                    <textarea class="summernote"><?php echo htmlspecialchars($blue_content); ?></textarea>
                    <button type="button" class="save-btn">저장</button>
                    <button type="button" class="cancel-btn">취소</button>
                </form>
            </div>
        </div>
        <div class="blz-note-item page-content" data-slug="<?php echo $owen_slug; ?>">
            <div class="view-mode">
                <div class="content-display"><?php echo $owen_content; ?></div>
                <?php if ($is_admin): ?><button type="button" class="edit-btn">수정</button><?php endif; ?>
            </div>
            <div class="edit-mode" style="display: none;">
                <form class="content-form">
                    <textarea class="summernote"><?php echo htmlspecialchars($owen_content); ?></textarea>
                    <button type="button" class="save-btn">저장</button>
                    <button type="button" class="cancel-btn">취소</button>
                </form>
            </div>
        </div>
    </div>
</div>