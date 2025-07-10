<?php
// /pages/timeline_upload.php

if (!$is_admin) {
    echo "권한이 없습니다.";
    exit;
}
$gallery_type = 'timeline'; 
?>
<div class="form-page-container">
    <a class="gallery_form_title">새 timeline 세션 기록</a>
    <form action="timeline_save.php" method="post">
        <input
            type="hidden"
            name="gallery_type"
            value="<?php echo htmlspecialchars($gallery_type); ?>">
        <div class="form-group">
            <label for="title">세션 제목</label>
            <input type="text" id="title" name="title" required="required">
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 이미지 (선택 사항)</label>
            <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
            <input type="hidden" id="thumbnail" name="thumbnail" value="">
        </div>
        <div class="form-group">
            <label for="content">세션 내용</label>
            <textarea id="summernote" name="content"></textarea>
        </div>
        <button type="submit">저장하기</button>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    </form>
</div>

<style>

    .form-page-container {
        width: 1170px;
        margin-left: 40px;
    }

    .form-page-container h1 {
        text-align: center;
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 40px;
        margin-top: 45px;
        margin-bottom: 15px;
    }

    .gallery_form_title {
        display: block;
        text-align: center;
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 40px;
        margin-top: 45px;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 30px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 24px;
        font-family: 'Fre7';
        color: rgba(255, 255, 255, 0.9);
    }

    .form-group input[type="text"],
    .form-group textarea {
        width: calc(100% - 20px);
        padding: 12px;
        border-radius: 5px;
        border: 1px solid rgba(198, 196, 196, 0.3);
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        font-family: 'Fre3';
        font-size: 20px;
        transition: border-color 0.3s ease;
    }

    .note-editor.note-frame {
        background-color: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 5px;
    }
    .note-editor .note-editing-area .note-editable {
        background-color: rgba(0, 0, 0, 0.4);
        color: white;
        min-height: 300px;
        padding: 15px;
    }

    .note-toolbar {
        background-color: rgba(0, 0, 0, 0.7);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }
    .note-btn {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-color: transparent !important;
    }
    .note-btn:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }
    .note-statusbar {
        background-color: rgba(0, 0, 0, 0.7);
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
    }

    button[type="submit"] {
        display: inline-block;
        padding: 12px 25px;
        background-color: rgb(255, 255, 255);
        color: black;
        border: none;
        border-radius: 5px;
        font-size: 20px;
        cursor: pointer;
        margin-right: 10px;
        font-family: 'Fre9';
        margin-bottom: 38px;
    }

    button[type="submit"]:hover {
        background-color: #e9e9e9;
    }

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
        overflow-y: auto;
        overflow-x: hidden;
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
</style>

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
                error: function (data) {
                    console.error("Image upload failed:", data);
                    alert("이미지 업로드 실패.");
                }
            });
        }
    });
</script>