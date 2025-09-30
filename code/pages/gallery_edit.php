<?php
require_once '../includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }

// URL 파라미터에서 수정할 게시물의 id를 가져옵니다. intval()로 숫자로 강제 변환합니다.
$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물입니다."); }

// 해당 id를 가진 게시물의 모든 정보를 DB에서 가져옵니다.
$stmt = $mysqli->prepare("SELECT * FROM chan_gallery WHERE id = ?");
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
        
        <label for="title">제목</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

        <label for="is_private">비밀글 설정</label>
        <input type="checkbox" id="is_private" name="is_private" value="1" <?php if($post['is_private']) echo 'checked'; ?>>
        <input type="password" id="password" name="password" placeholder="비밀번호 변경 시에만 입력" style="<?php if(!$post['is_private']) echo 'display:none;'; ?> margin-top: 10px;">

        <label for="content">내용</label>
        <textarea class="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        
        <button type="submit">수정 완료</button>
        <a class="cancel_btn" href="#/gallery_view?id=<?php echo $post_id; ?>">취소</a>
    </form>
</div>
<script>
// Summernote 에디터를 활성화합니다.
$('.summernote').summernote({ /* ... */ });
// '비밀글' 체크박스를 클릭하면 비밀번호 입력란을 보여주거나 숨깁니다.
$('#is_private').on('change', function() {
    if ($(this).is(':checked')) {
        $('#password').show();
    } else {
        $('#password').hide().val(''); // 숨기면서 입력된 값도 지웁니다.
    }
});
</script>