<?php
require_once '../includes/db.php';
$page_slug = 'chanlan';
//

$stmt = $mysqli->prepare("SELECT content FROM chan_pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$page_content = $stmt->get_result()->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다.</p>';
$stmt->close();
?>

<div class="page-container">
    <div id="view-mode">
        <div class="content-display"><?php echo $page_content; ?></div>
        <?php if ($is_admin): ?><button class="edit-btn">수정하기</button><?php endif; ?>
    </div>
    <?php if ($is_admin): // 아래는 관리자에게만 보이는 숨겨진 수정 영역입니다. ?>
    <div id="edit-mode" style="display:none;">
        <form class="ajax-form" action="ajax_save_page.php" method="post">
            <input type="hidden" name="slug" value="<?php echo $page_slug; ?>">
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장하기</button> 
            <button type="button" class="cancel-btn">취소</button>
        </form>
    </div>
    <script>
    $('.edit-btn').click(function() {
        $('#view-mode').hide();
        $('#edit-mode').show();
        $('.summernote').summernote({
            height: 400,
            focus: true,
            callbacks: { onImageUpload: function(files) { uploadSummernoteImage(files[0], $(this)); } }
        });
    });
    $('.cancel-btn').click(function() {
        $('.summernote').summernote('destroy');
        $('#edit-mode').hide();
        $('#view-mode').show();
    });
    </script>
    <?php endif; // 관리자 전용 영역 끝 ?>
</div>