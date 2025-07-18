<?php
// --- 파일 경로: /pages/trpg_upload.php (최종 수정본) ---
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "권한이 없습니다."; exit;
}
?>
<div class="form-page-container">
    <h2>새 게시물 작성</h2>
    <form action="../actions/gallery_save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="type" value="gallery">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 이미지 (선택)</label>
            <input type="file" id="thumbnail_file" name="thumbnail">
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"></textarea>
        </div>
        <button type="submit">저장하기</button>
    </form>
</div>

<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 350,
        callbacks: {
            onImageUpload: function(files) {
                uploadSummernoteImage(files[0], $(this));
            }
        }
    });

    function uploadSummernoteImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: '../actions/ajax_upload_image.php',
            type: "POST", data: data,
            contentType: false, processData: false, dataType: 'json',
            success: function(response) {
                if (response.success && response.urls) {
                    response.urls.forEach(url => editor.summernote('insertImage', url));
                } else {
                    alert('이미지 업로드 실패: ' + (response.error || '알 수 없는 오류'));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    }
});
</script>