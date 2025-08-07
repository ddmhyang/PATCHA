<?php
// /pages/gallery_edit.php
require_once '../includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물입니다."); }

$stmt = $mysqli->prepare("SELECT * FROM home_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 없습니다."); }
?>
<div class="form-page-container">
    <h2>게시물 수정</h2>
    <form class="ajax-form" action="ajax_save_gallery.php" method="post">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($post['gallery_type']); ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <div class="form-footer">
            <button type="submit" class="btn">수정 완료</button>
            <a href="#/gallery_view?id=<?php echo $post_id; ?>" class="btn">취소</a>
        </div>
    </form>
</div>
<script>
$('.summernote').summernote({
    height: 400,
    callbacks: { onImageUpload: function(files) { uploadSummernoteImage(files[0], $(this)); } }
});
</script>