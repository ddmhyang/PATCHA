<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// (PHP 코드는 기존과 동일)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p>권한이 없습니다.</p>"; exit;
}
if (!isset($_GET['id'])) {
    echo "<p>잘못된 접근입니다.</p>"; exit;
}

$post_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<p>존재하지 않는 게시글입니다.</p>"; exit;
}
$post = $result->fetch_assoc();
?>

<div class="form-container">
    <div class="gallery-content2">
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
                <textarea id="content" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            <button type="submit" class="btn-submit">수정하기</button>
            <a href="index.php?page=gallery_view&id=<?php echo $post['id']; ?>" class="btn-cancel">취소</a>
        </form>
    </div>
</div>

<script>
    // Summernote 에디터 초기화
    $('#content').summernote({
        height: 400,
        // ▼▼▼ 이 부분을 추가하세요 ▼▼▼
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana', 'HBIOS-SYS', 'Gravitas One'],
        fontNamesIgnoreCheck: ['HBIOS-SYS', 'Gravitas One'],
        // ▲▲▲ 여기까지 추가 ▲▲▲
        callbacks: {
            onImageUpload: function(files) {
                let data = new FormData();
                data.append("file", files[0]);
                $.ajax({
                    url: 'ajax_upload_image.php',
                    type: "POST",
                    data: data,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.url) {
                            $('#content').summernote('insertImage', response.url);
                        } else {
                            alert('이미지 업로드 실패: ' + response.error);
                        }
                    }
                });
            }
        }
    });

    // 썸네일 업로드 처리
    $('#thumbnail-file').on('change', function() {
        let formData = new FormData();
        formData.append('thumbnail', this.files[0]); // 썸네일은 'thumbnail'이라는 이름으로 보냅니다.
        $.ajax({
            url: 'ajax_upload_image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json', // JSON으로 응답받을 것을 명시
            success: function(response) {
                // 더 이상 JSON.parse()를 사용하지 않습니다.
                if (response.url) {
                    $('#thumbnail').val(response.url);
                    $('#thumbnail-preview').html('<img src="' + response.url + '" style="max-width: 200px; margin-top: 10px;">');
                } else if (response.error) {
                    alert('썸네일 업로드 실패: ' + response.error);
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
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alert('게시글이 수정되었습니다.');
                    // ▼▼▼ 이 부분을 수정합니다 ▼▼▼
                    // location.href 대신 handleNavigation 함수를 사용합니다.
                    handleNavigation('index.php?page=gallery_view&id=' + formData.id);
                } else {
                    alert('저장 실패: ' + response.message);
                }
            }
        });
    });
</script>