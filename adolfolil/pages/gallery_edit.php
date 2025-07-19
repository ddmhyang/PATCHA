<?php
// --- 파일 경로: /pages/gallery_edit.php ---
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { exit("권한이 없습니다."); }
if (!isset($_GET['id'])) { exit("잘못된 접근입니다."); }

$post_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ? AND type = 'gallery'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) { exit("게시물이 없습니다."); }
?>
<div class="form-page-container">
    <h2>게시물 수정</h2>
    <form action="actions/gallery_save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="type" value="gallery">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 (변경 시에만 업로드)</label>
            <input type="file" id="thumbnail_file" name="thumbnail">
            <?php if(!empty($post['thumbnail_path'])): ?>
                <p>현재 이미지: <img src="<?php echo $post['thumbnail_path']; ?>" width="100"></p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <button type="submit">수정 완료</button>
        <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="btn-cancel">취소</a>
    </form>
</div>
<script>
    $(document).ready(function() {
        $('.summernote').summernote({ height: 350, /* ... onImageUpload 콜백 ... */ });
    });
</script>