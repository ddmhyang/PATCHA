<?php
// 로그인 상태가 아니면 갤러리 목록으로 돌려보냅니다.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='index.php?page=gallery';</script>";
    exit;
}
?>

<div class="form-container">
    <h2>새 게시글 작성</h2>
    <form id="gallery-upload-form">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="thumbnail">썸네일 이미지</label>
            <input type="file" id="thumbnail-file" accept="image/*">
            <input type="hidden" id="thumbnail" name="thumbnail">
            <div id="thumbnail-preview"></div>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="content" name="content"></textarea>
        </div>
        <button type="submit" class="btn-submit">저장하기</button>
        <a href="index.php?page=gallery" class="btn-cancel">취소</a>
    </form>
</div>

<script>
$(document).ready(function() {
    // Summernote 에디터 초기화
    $('#content').summernote({
        height: 400,
        callbacks: {
            onImageUpload: function(files) {
                // 이미지 업로드 처리를 위해 chanlan의 ajax_upload_image.php 로직 필요
                // 해당 파일을 sHotel/ajax_upload_image.php 로 생성해야 합니다.
                let data = new FormData();
                data.append("file", files[0]);
                $.ajax({
                    url: 'ajax_upload_image.php',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: data,
                    type: "POST",
                    success: function(url) {
                        $('#content').summernote('insertImage', url);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error(textStatus + " " + errorThrown);
                    }
                });
            }
        }
    });

    // 썸네일 업로드 처리
    $('#thumbnail-file').on('change', function() {
        let formData = new FormData();
        formData.append('thumbnail', this.files[0]);
        $.ajax({
            url: 'ajax_upload_image.php', // 이미지 업로드 API 재사용
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                let data = JSON.parse(response);
                if(data.url) {
                    $('#thumbnail').val(data.url);
                    $('#thumbnail-preview').html('<img src="' + data.url + '" style="max-width: 200px; margin-top: 10px;">');
                } else if(data.error) {
                    alert(data.error);
                }
            }
        });
    });

    // 폼 제출 처리
    $('#gallery-upload-form').on('submit', function(e) {
        e.preventDefault();
        let formData = {
            title: $('#title').val(),
            thumbnail: $('#thumbnail').val(),
            content: $('#content').summernote('code')
        };

        $.ajax({
            url: 'ajax_save_gallery.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                alert('게시글이 저장되었습니다.');
                location.href = 'index.php?page=gallery';
            }
        });
    });
});
</script>