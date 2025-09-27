<?php
session_start(); // 이 줄을 추가하세요!

// 로그인 상태가 아니면 갤러리 목록으로 돌려보냅니다.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='pages.php#gallery';</script>";
    exit;
}
?>

<div class="form-container">
    <div class="gallery-content2">
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
            <a href="pages.php#gallery" class="btn-cancel">취소</a>
        </form>
    </div>
</div>

<script>
    $('#content').summernote({
        height: 400,
        // ▼▼▼ 이 부분을 추가하세요 ▼▼▼
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana', 'HBIOS-SYS', 'Gravitas One'],
        fontNamesIgnoreCheck: ['HBIOS-SYS', 'Gravitas One'],
        // ▲▲▲ 여기까지 추가 ▲▲▲
        callbacks: {
            onImageUpload: function(files) {
                let data = new FormData();
                data.append("file", files[0]); // Summernote는 'file'이라는 이름으로 보냅니다.
                $.ajax({
                    url: 'ajax_upload_image.php',
                    type: "POST",
                    data: data,
                    contentType: false,
                    processData: false,
                    dataType: 'json', // JSON으로 응답받을 것을 명시
                    success: function(response) {
                        if (response.url) {
                            // 소포(response)에서 주소(url)를 정확히 꺼내서 사용합니다.
                            $('#content').summernote('insertImage', response.url);
                        } else {
                            alert('이미지 업로드 실패: ' + response.error);
                        }
                    }
                });
            }
        }
    });

    // 썸네일 업로드 처리
    $('#thumbnail-file').on('change', function() {
        let formData = new FormData();
        formData.append('thumbnail', this.files[0]); // 썸네일은 'thumbnail'이라는 이름으로 보냅니다.
        $.ajax({
            url: 'ajax_upload_image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // JSON으로 응답받을 것을 명시
            success: function(response) {
                // 더 이상 JSON.parse()를 사용하지 않습니다.
                if (response.url) {
                    $('#thumbnail').val(response.url);
                    $('#thumbnail-preview').html('<img src="' + response.url + '" style="max-width: 200px; margin-top: 10px;">');
                } else if (response.error) {
                    alert('썸네일 업로드 실패: ' + response.error);
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
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alert('게시글이 저장되었습니다.');
                    // 'navigate' 라는 이름으로 방송을 보냅니다.
                    $(document).trigger('navigate', { url: 'index.php?page=gallery' });
                } else {
                    alert('저장 실패: ' + response.message);
                }
            }
        });
    });
</script>