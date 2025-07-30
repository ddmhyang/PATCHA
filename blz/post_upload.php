<?php
// /blz/post_upload.php
require_once 'includes/db.php';
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) { exit; }
$type = $_GET['type'] ?? 'art';
?>

<div class="form-page-container">
    <h1><?php echo ucfirst($type); ?> 갤러리 새 글 작성</h1>
    <form id="post-form" action="ajax_save_post.php" method="post">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 (선택, 미지정 시 본문 첫 이미지 자동 등록)</label>
            <input type="file" id="thumbnail_file" accept="image/*">
            <input type="hidden" id="thumbnail_path" name="thumbnail" value="">
            <div id="thumbnail-preview" class="thumbnail-preview"></div>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="summernote-post" name="content"></textarea>
        </div>
        <button type="submit" class="btn-submit">저장하기</button>
        <a href="#/<?php echo htmlspecialchars($type); ?>" class="btn-cancel">취소</a>
    </form>
</div>