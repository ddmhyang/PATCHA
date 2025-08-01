<?php
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo "권한이 없습니다."; exit;
}
?>
<div class="form-page-container">
    <h2>새 TRPG 세션 기록</h2>
    <form action="trpg_save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="type" value="trpg">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <input type="text" id="title" name="title" required>
            <label for="title">제목</label>
        </div>

        <div class="form-group">
            <label for="writer_name">W.</label>
            <input type="text" id="writer_name" name="writer_name">
        </div>
        <div class="form-group">
            <label for="kpc_name">KPC</label>
            <input type="text" id="kpc_name" name="kpc_name">
        </div>
        <div class="form-group">
            <label for="pc_name">PC</label>
            <input type="text" id="pc_name" name="pc_name">
        </div>
        <div class="form-group">
            <label for="trpg_rule">사용 룰</label>
            <input type="text" id="trpg_rule" name="trpg_rule">
        </div>
        
        <div class="form-group">
            <label for="thumbnail_file">썸네일 이미지 (선택)</label>
            <input type="file" id="thumbnail_file" name="thumbnail">
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"></textarea>
        </div>
        <div class="view-footer">
            <button type="submit" class="btn btn-primary">저장하기</button>
        </div>
    </form>
</div>


<script>
$(document).ready(function() {
    
    
    if ($('.summernote').length > 0) {
        $('.summernote').summernote('destroy');
    }
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
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
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
    $(document).off('submit', 'form[action="trpg_save.php"]').on('submit', 'form[action="trpg_save.php"]', function(e) {
        e.preventDefault();

        $('textarea[name="content"]').val($('.summernote').summernote('code'));

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.hash = response.redirect_url;
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function() {
                alert('서버와 통신 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>