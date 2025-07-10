<?php
// /pages/gallery_edit.php

if (!$is_admin) {
    echo "<h1>권한이 없습니다.</h1>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<h1>잘못된 접근입니다.</h1>";
    exit;
}
$post_id = intval($_GET['id']);

$stmt = $mysqli->prepare("SELECT * FROM eden_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "<h1>게시물이 존재하지 않습니다.</h1>";
    exit;
}
?>

<div class="form-page-container">
    <h1>게시물 수정</h1>
    <form action="gallery_save.php" method="post">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input
            type="hidden"
            name="gallery_type"
            value="<?php echo htmlspecialchars($post['gallery_type']); ?>">

        <div class="form-group">
            <label for="title">제목</label>
            <input
                type="text"
                id="title"
                name="title"
                value="<?php echo htmlspecialchars($post['title']); ?>"
                required="required">
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
            <textarea id="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <button type="submit">수정 완료</button>
        <a
            href="main.php?page=gallery_view&id=<?php echo $post['id']; ?>"
            class="btn-cancel">취소</a>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    </form>
</div>

<script>
    $(document).ready(function () {
        $('#summernote').summernote({
            height: 400,
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

    .form-page-container h1 {
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

    .btn-cancel {
        margin-left: 15px;
        display: inline-block;
        padding: 12px 25px;
        background-color: #000000;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 20px;
        text-decoration: none;
        font-family: 'Fre9';
    }
</style>