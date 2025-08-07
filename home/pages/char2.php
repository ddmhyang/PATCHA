<?php
require_once '../includes/db.php';
$page_slug = 'char2';

$stmt = $mysqli->prepare("SELECT content FROM home_pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$page_content = $stmt->get_result()->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다.</p>';
$stmt->close();
?>
<div class="page-container" data-slug="<?php echo $page_slug; ?>">
    <div id="view-mode">
        <div class="content-display"><?php echo $page_content; ?></div>
        <?php if ($is_admin): ?>
        <div class="admin-controls"><button type="button" id="edit-btn">수정하기</button></div>
        <?php endif; ?>
    </div>

    <?php if ($is_admin): ?>
    <div id="edit-mode" style="display: none;">
        <form class="ajax-form" action="ajax_save_page.php" method="post">
            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($page_slug); ?>">
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장</button>
            <button type="button" id="cancel-btn">취소</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('#edit-btn').on('click', function() {
        $('#view-mode').hide();
        $('#edit-mode').show();
        $('.summernote').summernote({
            height: 400, focus: true,
            callbacks: { onImageUpload: function(files) { uploadSummernoteImage(files[0], $(this)); } }
        });
    });
    $('#cancel-btn').on('click', function() {
        $('.summernote').summernote('destroy');
        $('#edit-mode').hide();
        $('#view-mode').show();
    });
});
</script>