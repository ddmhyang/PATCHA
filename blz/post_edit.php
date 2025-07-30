<?php
// /blz/post_edit.php
require_once 'includes/db.php';
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) { exit; }
if (!isset($_GET['id'])) { exit; }
$post_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM blz_posts WHERE id = ?");
$stmt->bind_param("i", $post_id); $stmt->execute();
$post = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$post) { exit; }
?>

<div class="form-page-container">
    <h1><?php echo ucfirst($post['type']); ?> 갤러리 글 수정</h1>
    <form id="post-form" action="ajax_save_post.php" method="post">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($post['type']); ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 (선택, 미지정 시 본문 첫 이미지 자동 등록)</label>
            <input type="file" id="thumbnail_file" accept="image/*">
            <input type="hidden" id="thumbnail_path" name="thumbnail" value="<?php echo htmlspecialchars($post['thumbnail_path'] ?? ''); ?>">
            <div id="thumbnail-preview" class="thumbnail-preview">
                <?php if (!empty($post['thumbnail_path'])): ?>
                    <img src="<?php echo htmlspecialchars($post['thumbnail_path']); ?>" alt="Thumbnail Preview">
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="summernote-post" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <button type="submit" class="btn-submit">수정 완료</button>
        <a href="#/post_view?id=<?php echo $post['id']; ?>" class="btn-cancel">취소</a>
    </form>
</div>