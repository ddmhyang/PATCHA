<?php
// /pages/timeline_add.php (신규)
if (!$is_admin) {
    echo "<h1>권한이 없습니다.</h1>";
    exit;
}
?>
<style>
    /* 여기에 폼 페이지에 필요한 스타일을 추가합니다. */
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
    
    .form-container {
        color: white;
        padding: 40px;
        box-sizing: border-box;
        height: 100%;
        overflow-y: auto;
    }
    .form-container::-webkit-scrollbar {
        width: 8px;
    }
    .form-container::-webkit-scrollbar-thumb {
        background-color: #555;
        border-radius: 4px;
    }
    .form-container::-webkit-scrollbar-track {
        background-color: #333;
    }
    .form-container h1 {
        font-family: 'Fre7';
    }
    .form-container form label {
        display: block;
        margin: 15px 0 5px;
        font-family: 'Fre9';
    }
    .form-container form input[type="text"],
    .form-container form input[type="file"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        background: #333;
        color: white;
        border: 1px solid #555;
    }
    .form-container form button {
        font-family: 'Fre7';
        background: white;
        color: black;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 5px;
        margin-top: 20px;
    }
    .form-container form .btn-cancel {
        background: black;
        color: white;
        text-decoration: none;
    }
</style>

<div class="form-container">
    <h1>새 스토리 추가</h1>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <form action="timeline_save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <label for="year">제목:</label>
        <input type="text" id="year" name="year" required="required">
        <label for="thumbnail_file">썸네일 이미지:</label>
        <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
        <label for="add-summernote">상세 설명:</label>
        <textarea id="add-summernote" name="full_description"></textarea>
        <button type="submit">추가하기</button>
        <a
            href="#/timeline"
            class="btn-cancel"
            style="padding: 10px 15px; margin-left: 10px;">취소</a>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('#add-summernote').summernote({height: 300, dialogsInBody: true});
    });
</script>