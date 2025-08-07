<?php
require_once '../includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }
$gallery_type = $_GET['type'] ?? 'gallery';
?>
<div class="form-page-container">
    <h2>새 게시물 작성 (<?php echo ucfirst($gallery_type); ?>)</h2>
    <form class="ajax-form" action="ajax_save_gallery.php" method="post">
        <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($gallery_type); ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"></textarea>
        </div>
        <div class="form-footer">
            <button type="submit" class="btn">저장하기</button>
            <a href="#/<?php echo htmlspecialchars($gallery_type); ?>" class="btn">취소</a>
        </div>
    </form>
</div>
<script>
$('.summernote').summernote({
    height: 400,
    callbacks: { onImageUpload: function(files) { uploadSummernoteImage(files[0], $(this)); } }
});
</script>