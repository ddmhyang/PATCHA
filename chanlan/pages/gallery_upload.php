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
            <label for="is_private">비밀글 설정</label>
            <input type="checkbox" id="is_private" name="is_private" value="1">
            <input type="password" id="password" name="password" placeholder="비밀번호 (비밀글 체크 시 입력)" style="display:none; margin-top: 10px;">
        </div>
        
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"></textarea>
        </div>
        <button type="submit">저장하기</button>
        <a href="#/<?php echo htmlspecialchars($gallery_type); ?>">취소</a>
    </form>
</div>
<script>
// Summernote 초기화
$('.summernote').summernote({
    height: 400,
    callbacks: { onImageUpload: function(files) { uploadSummernoteImage(files[0], $(this)); } }
});

// 비밀글 체크박스에 따라 비밀번호 입력창 보이기/숨기기
$('#is_private').on('change', function() {
    if ($(this).is(':checked')) {
        $('#password').show().prop('required', true);
    } else {
        $('#password').hide().prop('required', false).val('');
    }
});
</script>