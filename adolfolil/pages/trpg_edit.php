<?php
require_once __DIR__ . '/../includes/db.php';
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { exit("권한이 없습니다."); }
if (!isset($_GET['id'])) { exit("잘못된 접근입니다."); }

$post_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ? AND type = 'trpg'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) { exit("게시물이 없습니다."); }
?>
<div class="form-page-container">
    <h2>게시물 수정</h2>
    <form action="trpg_save.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="type" value="trpg">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="writer_name">W.</label>
            <input type="text" id="writer_name" name="writer_name" value="<?php echo htmlspecialchars($post['writer_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="kpc_name">KPC</label>
            <input type="text" id="kpc_name" name="kpc_name" value="<?php echo htmlspecialchars($post['kpc_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="pc_name">PC</label>
            <input type="text" id="pc_name" name="pc_name" value="<?php echo htmlspecialchars($post['pc_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="trpg_rule">사용 룰</label>
            <input type="text" id="trpg_rule" name="trpg_rule" value="<?php echo htmlspecialchars($post['trpg_rule'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="thumbnail_file">썸네일 (변경 시에만 업로드)</label>
            <input type="file" id="thumbnail_file" name="thumbnail">
            <?php if(!empty($post['thumbnail_path'])): ?>
                <p>현재 이미지: <img src="/<?php echo htmlspecialchars($post['thumbnail_path']); ?>" width="100"></p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <div class="view-footer">
            <button type="submit" class="btn btn-primary">수정 완료</button>
            <a href="#/trpg_view?id=<?php echo $post['id']; ?>" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>
<script>
$(document).ready(function() {
    
    
    if ($('.summernote').length > 0) {
        $('.summernote').summernote('destroy');
    }
    $('.summernote').summernote({
        height: 350,
        callbacks: {
            onImageUpload: function(files) {
                uploadSummernoteImage(files[0], $(this));
            }
        }
    });

    
    function uploadSummernoteImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.urls) {
                    response.urls.forEach(url => editor.summernote('insertImage', url));
                } else {
                    alert('이미지 업로드 실패: ' + (response.error || '알 수 없는 오류'));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    }
    $(document).off('submit', 'form[action="trpg_save.php"]').on('submit', 'form[action="trpg_save.php"]', function(e) {
        e.preventDefault();

        $('textarea[name="content"]').val($('.summernote').summernote('code'));

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.hash = response.redirect_url;
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function() {
                alert('서버와 통신 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>