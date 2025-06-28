<?php
// /pages/page_view.php
// main.php를 통해 로드되므로, $is_admin, $mysqli 변수는 이미 사용 가능합니다.

// 1. URL로부터 어떤 페이지를 보여줄지 'name' 파라미터를 받습니다.
if (!isset($_GET['name'])) {
    echo "<h1>표시할 페이지를 지정해주세요.</h1>";
    exit;
}
$page_name = $_GET['name'];

// 2. DB에서 해당 페이지의 컨텐츠 불러오기
$stmt = $mysqli->prepare("SELECT content FROM eden_pages_content WHERE page_name = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content_row = $result->fetch_assoc();
$page_content = $page_content_row['content'] ?? '<h2>아직 작성된 콘텐츠가 없습니다.</h2><p>관리자로 로그인하여 내용을 추가해주세요.</p>';
$stmt->close();
?>

<div class="page-container">
    <?php if ($is_admin): ?>
        <form id="edit-form" action="ajax_save_page.php" method="post">
            <input type="hidden" name="page_name" value="<?php echo htmlspecialchars($page_name); ?>">
            <textarea id="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장하기</button>
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        </form>
    <?php else: ?>
        <div class="content-display">
            <?php echo $page_content; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Summernote 초기화 로직은 이전과 동일합니다.
    $('#summernote').summernote({
        height: 300,
        callbacks: {
            onImageUpload: function(files) {
                uploadImage(files[0], $(this));
            }
        }
    });

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

    // 폼 제출 (AJAX 방식)
    $('#edit-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    alert('저장되었습니다.');
                } else {
                    alert('저장 실패: ' + response.message);
                }
            },
            dataType: 'json'
        });
    });
});
</script>