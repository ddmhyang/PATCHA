<?php
// /pages/timeline_edit.php (신규)
if (!$is_admin) {
    echo "<h1>권한이 없습니다.</h1>";
    exit;
}
$item_id = intval($_GET['id'] ?? 0);
if ($item_id <= 0) { echo "<h1>잘못된 접근입니다.</h1>"; exit; }

$stmt = $mysqli->prepare("SELECT * FROM eden_timeline WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$item) { echo "<h1>게시물이 존재하지 않습니다.</h1>"; exit; }
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
    .form-container img {
        max-width: 100px;
        display: block;
        margin-top: 10px;
    }
</style>

<div class="form-container">
    <h1>스토리 수정</h1>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <form action="timeline_save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input
            type="hidden"
            name="existing_thumbnail"
            value="<?php echo htmlspecialchars($item['thumbnail']); ?>">

        <label for="year">제목:</label>
        <input
            type="text"
            id="year"
            name="year"
            value="<?php echo htmlspecialchars($item['year']); ?>"
            required="required">

        <label for="thumbnail_file">썸네일 이미지 (변경 시에만 선택):</label>
        <?php if (!empty($item['thumbnail'])): ?>
        <p>현재 썸네일:</p>
        <img
            src="<?php echo htmlspecialchars($item['thumbnail']); ?>"
            alt="Current Thumbnail">
        <?php endif; ?>
        <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">

        <label for="edit-summernote">상세 설명:</label>
        <textarea id="edit-summernote" name="full_description"><?php echo htmlspecialchars($item['full_description']); ?></textarea>

        <button type="submit">수정 완료</button>
        <a
            href="#/timeline_detail?id=<?php echo $item['id']; ?>"
            class="btn-cancel"
            style="padding: 10px 15px; margin-left: 10px;">취소</a>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('#edit-summernote').summernote({height: 300, dialogsInBody: true});
    });
</script>