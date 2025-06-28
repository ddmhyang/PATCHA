<?php
// /pages/gallery_upload.php
if (!$is_admin) {
    echo "권한이 없습니다.";
    exit;
}
$gallery_type = $_GET['type'] ?? 'gallery1'; // 어떤 갤러리에 쓸지 type으로 받음
?>
<div class="gallery-upload-container">
    <h1>새 글 작성 (<?php echo htmlspecialchars($gallery_type); ?>)</h1>
    <form action="gallery_save.php" method="post">
        <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($gallery_type); ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="summernote" name="content"></textarea>
        </div>
        <button type="submit">저장하기</button>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    </form>
</div>

<script>
// Summernote 초기화 스크립트는 main.php 또는 이 페이지 하단에 위치해야 합니다.
// 중복을 피하기 위해 main.php에 있는 스크립트를 활용합니다.
$(document).ready(function() {
    $('#summernote').summernote({
        height: 400,
        callbacks: {
            onImageUpload: function(files) {
                uploadImage(files[0], $(this));
            }
        }
    });

    function uploadImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "POST",
            success: function(url) {
                editor.summernote('insertImage', url);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error(textStatus + " " + errorThrown);
            }
        });
    }
});
</script>