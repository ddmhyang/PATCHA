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
    @media (max-width: 768px) {
        .content {
            position: absolute !important;
            top: 273px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 900px;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
            padding: 0;
            box-sizing: border-box;
        }
        .form-page-container {
            width: 520px;
            margin-left: 40px;
        }
    }
</style>