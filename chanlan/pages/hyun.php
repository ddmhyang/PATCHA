<?php
require_once '../includes/db.php';
$page_slug = 'hyun';

$stmt = $mysqli->prepare("SELECT content FROM chan_pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$page_content = $stmt->get_result()->fetch_assoc()['content'] ?? '';
$stmt->close();
?>
<div class="page-container">
    <div id="view-mode"><?php echo $page_content; ?>
        <?php if ($is_admin): ?><button class="edit-btn">수정</button><?php endif; ?>
    </div>
    <?php if ($is_admin): ?>
    <div id="edit-mode" style="display:none;">
        <form class="ajax-form" action="ajax_save_page.php" method="post">
            <input type="hidden" name="slug" value="<?php echo $page_slug; ?>">
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장</button> <button type="button" class="cancel-btn">취소</button>
        </form>
    </div>
    <script>
    $('.edit-btn').click(()=>{$('#view-mode').hide();$('#edit-mode').show();$('.summernote').summernote({height:400,callbacks:{onImageUpload:(f,e)=>uploadSummernoteImage(f[0],e)}});});
    $('.cancel-btn').click(()=>{$('.summernote').summernote('destroy');$('#edit-mode').hide();$('#view-mode').show();});
    </script>
    <?php endif; ?>
</div>