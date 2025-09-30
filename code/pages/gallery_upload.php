<?php
require_once '../includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }
// URL 파라미터로부터 어떤 타입('gallery' 또는 'trpg')의 글을 작성하는지 받아옵니다.
$gallery_type = $_GET['type'] ?? 'gallery';
?>
<div class="form-page-container">
    <h2>새 글 작성 (<?php echo ucfirst($gallery_type); ?>)</h2>
    <form class="ajax-form" action="ajax_save_gallery.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($gallery_type); ?>">
        
        <label for="title">제목</label>
        <input type="text" id="title" name="title" required>
        
        <label for="thumbnail">썸네일 (선택, 없으면 본문 첫 이미지 자동 등록)</label>
        <label for="thumbnail" class="file-upload-button">파일 선택</label>
        <input type="file" id="thumbnail" name="thumbnail" style="display: none;">
        
        <label><input type="checkbox" id="is_private" name="is_private" value="1"> 비밀글</label>
        <input type="password" id="password" name="password" placeholder="비밀번호 (비밀글 체크 시 입력)" style="display:none;">
        
        <label for="content">내용</label>
        <textarea class="summernote" name="content"></textarea>
        
        <button type="submit">저장하기</button>
        <a class="cancel_btn" href="#/<?php echo htmlspecialchars($gallery_type); ?>">취소</a>
    </form>
</div>
<script>
// Summernote 에디터를 활성화합니다.
$('.summernote').summernote({
    height: 400,
    callbacks: {
        onImageUpload: function(files) {
            uploadSummernoteImage(files[0], $(this));
        }
    }
});
// '비밀글' 체크박스의 상태가 변경될 때마다 실행됩니다.
$('#is_private').on('change', function() {
    // 체크박스가 체크되면(.checked) 비밀번호 입력란을 보여주고(toggle) 필수 입력(required)으로 설정합니다.
    $('#password').toggle(this.checked).prop('required', this.checked);
});
</script>