<?php
// 로그인 상태 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<script>alert('권한이 없습니다.'); location.href='index.php?page=gallery';</script>";
    exit;
}

// 게시글 ID 확인
if (!isset($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='index.php?page=gallery';</script>";
    exit;
}

$post_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('존재하지 않는 게시글입니다.'); location.href='index.php?page=gallery';</script>";
    exit;
}
$post = $result->fetch_assoc();
?>

<div class="form-container">
    <h2>게시글 수정</h2>
    <form id="gallery-edit-form">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="thumbnail">썸네일 이미지</label>
            <input type="file" id="thumbnail-file" accept="image/*">
            <input type="hidden" id="thumbnail" name="thumbnail" value="<?php echo htmlspecialchars($post['thumbnail']); ?>">
            <div id="thumbnail-preview">
                <?php if (!empty($post['thumbnail'])): ?>
                    <img src="<?php echo htmlspecialchars($post['thumbnail']); ?>" style="max-width: 200px; margin-top: 10px;">
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="content" name="content"><?php echo $post['content']; ?></textarea>
        </div>
        <button type="submit" class="btn-submit">수정하기</button>
        <a href="index.php?page=gallery_view&id=<?php echo $post['id']; ?>" class="btn-cancel">취소</a>
    </form>
</div>

<script>
// gallery_upload.php와 동일한 스크립트 사용
$(document).ready(function() {
    $('#content').summernote({
        height: 400,
        callbacks: {
            onImageUpload: function(files) {
                let data = new FormData();
                data.append("file", files[0]);
                $.ajax({
                    url: 'ajax_upload_image.php',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: data,
                    type: "POST",
                    success: function(url) {
                        $('#content').summernote('insertImage', url);
                    }
                });
            }
        }
    });

    $('#thumbnail-file').on('change', function() {
        let formData = new FormData();
        formData.append('thumbnail', this.files[0]);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                let data = JSON.parse(response);
                if(data.url) {
                    $('#thumbnail').val(data.url);
                    $('#thumbnail-preview').html('<img src="' + data.url + '" style="max-width: 200px; margin-top: 10px;">');
                } else if(data.error) {
                    alert(data.error);
                }
            }
        });
    });

    $('#gallery-edit-form').on('submit', function(e) {
        e.preventDefault();
        let formData = {
            id: $('[name="id"]').val(),
            title: $('#title').val(),
            thumbnail: $('#thumbnail').val(),
            content: $('#content').summernote('code')
        };

        $.ajax({
            url: 'ajax_save_gallery.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                alert('게시글이 수정되었습니다.');
                location.href = 'index.php?page=gallery_view&id=' + formData.id;
            }
        });
    });
});
</script>