<?php
// /pages/gallery_edit.php
// main.php를 통해 로드되므로, $is_admin, $mysqli 변수는 이미 사용 가능합니다.

// 1. 관리자 권한 확인
if (!$is_admin) {
    echo "<h1>권한이 없습니다.</h1>";
    exit;
}

// 2. 수정할 게시물 ID가 있는지 확인
if (!isset($_GET['id'])) {
    echo "<h1>잘못된 접근입니다.</h1>";
    exit;
}
$post_id = intval($_GET['id']);

// 3. DB에서 기존 게시물 정보 불러오기
$stmt = $mysqli->prepare("SELECT * FROM eden_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

// 게시물이 존재하지 않을 경우
if (!$post) {
    echo "<h1>게시물이 존재하지 않습니다.</h1>";
    exit;
}
?>

<div class="gallery-edit-container">
    <h1>게시물 수정</h1>
    <form action="gallery_save.php" method="post">
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($post['gallery_type']); ?>">
        
        <div class="form-group">
            <label for="title">제목</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="content">내용</label>
            <textarea id="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <button type="submit">수정 완료</button>
        <a href="main.php?page=gallery_view&id=<?php echo $post['id']; ?>">취소</a>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    </form>
</div>

<script>
$(document).ready(function() {
    // Summernote 초기화
    $('#summernote').summernote({
        height: 400,
        // Summernote가 htmlspecialchars로 인코딩된 코드를 HTML로 올바르게 표시합니다.
        // 이미지 업로드 콜백은 이전과 동일하게 설정합니다.
        callbacks: {
            onImageUpload: function(files) {
                uploadImage(files[0], $(this));
            }
        }
    });

    // 이미지 업로드 함수 (이전 코드와 동일)
    function uploadImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: "POST",
            success: function(url) {
                editor.summernote('insertImage', url);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error(textStatus + " " + errorThrown);
            }
        });
    }
});
</script>