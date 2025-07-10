<?php
// /pages/gallery_upload.php
if (!$is_admin) {
    echo "권한이 없습니다.";
    exit;
}
$gallery_type = $_GET['type'] ?? 'gallery1'; 
?>
<div class="form-page-container">
    <a class="gallery_form_title">새 글 작성</a>
    <form action="gallery_save.php" method="post">
        <input
            type="hidden"
            name="gallery_type"
            value="<?php echo htmlspecialchars($gallery_type); ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" required="required">
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 이미지 (선택 사항)</label>
            <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
            <input type="hidden" id="thumbnail" name="thumbnail" value="">
            <div
                id="thumbnailPreview"
                style="margin-top: 15px; border: 1px solid #ddd; padding: 10px; background-color: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; height: 150px; overflow: hidden;">
                <img
                    id="currentThumbnail"
                    src=""
                    alt="썸네일 미리보기"
                    style="max-width: 100%; max-height: 100%; display: none;">
                <span id="noThumbnailText" style="color: rgba(255,255,255,0.7);">선택된 썸네일 없음</span>
            </div>
            <button
                type="button"
                id="autoSetThumbnail"
                style="margin-top: 10px; padding: 8px 15px; background-color: #555; color: white; border: none; border-radius: 5px; cursor: pointer;">본문 첫 이미지로 자동 설정</button>
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
    $(document).ready(function () {
        $('#summernote').summernote({
            height: 350,
            callbacks: {
                onImageUpload: function (files) {
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
                success: function (url) {
                    editor.summernote('insertImage', url);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(textStatus + " " + errorThrown);
                }
            });
        }
    });
    $('#thumbnail_file').on('change', function () {
        if (this.files && this.files[0]) {
            let formData = new FormData();
            formData.append('thumbnail_file', this.files[0]);

            $.ajax({
                url: 'ajax_upload_thumbnail.php', 
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        $('#thumbnail').val(response.file_url); 
                        $('#currentThumbnail')
                            .attr('src', response.file_url)
                            .show();
                        $('#noThumbnailText').hide();
                    } else {
                        alert('썸네일 업로드 실패: ' + response.message);
                        $('#thumbnail').val('');
                        $('#currentThumbnail')
                            .attr('src', '')
                            .hide();
                        $('#noThumbnailText').show();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('썸네일 업로드 AJAX 오류:', error);
                    alert('썸네일 업로드 중 오류가 발생했습니다.');
                    $('#thumbnail').val('');
                    $('#currentThumbnail')
                        .attr('src', '')
                        .hide();
                    $('#noThumbnailText').show();
                }
            });
        }
    });

    
    $('#autoSetThumbnail').on('click', function () {
        let content = $('#summernote').summernote('code');
        let imgMatch = content.match(/<img[^>]+src=\"([^\">]+)\"/);
        if (imgMatch && imgMatch[1]) {
            let imageUrl = imgMatch[1];
            $('#thumbnail').val(imageUrl); 
            $('#currentThumbnail')
                .attr('src', imageUrl)
                .show();
            $('#noThumbnailText').hide();
        } else {
            alert('본문에 이미지가 없습니다.');
            $('#thumbnail').val(''); 
            $('#currentThumbnail')
                .attr('src', '')
                .hide(); 
            $('#noThumbnailText').show(); 
        }
    });
</script>

<style>
   
    .content {
        position: absolute !important;
        top: 220px;
        left: 50%;
        transform: translateX(-50%);
        width: 1250px;
        height: 605px;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 0;
        box-sizing: border-box;
        overflow-x: hidden;
        overflow-y: scroll;
    }

    .content::-webkit-scrollbar {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background-color: #555;
        border-radius: 4px;
    }
    .content::-webkit-scrollbar-track {
        background-color: #333;
    }

   
    .form-page-container {
        width: 1170px;
       
        margin: 40px;
    }

    .gallery_form_title {
        text-align: center;
        color: white;
        font-family: 'Fre9';
        font-size: 36px;
    }

    .form-group {
        margin-top: 30px;
        margin-bottom: 30px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-family: 'Fre1', sans-serif;
        font-size: 16px;
        color: rgba(255, 255, 255, 0.9);
    }

    .form-group input[type="text"],
    .form-group textarea {
        width: calc(100% - 20px);
        padding: 10px;
        border-radius: 5px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        font-family: 'Fre1', sans-serif;
        font-size: 16px;
    }

   
    .note-editor.note-frame {
        background-color: rgba(0, 0, 0, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .note-editor .note-editing-area .note-editable {
        background-color: rgba(0, 0, 0, 0.3);
        color: white;
    }
    .note-toolbar {
        background-color: rgba(0, 0, 0, 0.6);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    .note-btn {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-color: transparent !important;
    }
    .note-btn:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }

    button[type="submit"] {
        display: block;
        width: fit-content;
        margin: 20px auto 0;
        padding: 10px 30px;
        background-color: rgb(0, 0, 0);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 20px;
        font-family: 'Fre9';
    }

    button[type="submit"]:hover {
        transform: scale(1.05);
    }
</style>