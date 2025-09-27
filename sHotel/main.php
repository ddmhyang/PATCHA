<?php

require_once __DIR__ . '/includes/db.php'; // 이 코드를 맨 위에 추가하세요.

// DB에서 main 페이지 콘텐츠 가져오기
$sql = "SELECT * FROM pages WHERE page_name = 'main'";
$result = $mysqli->query($sql);
$page_content = "";
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $page_content = $row['content'];
}
?>

<div class="page-container">
    <div id="main-content" class="editable-content">
        <?php echo $page_content; ?>
    </div>

    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <button id="edit-btn">수정하기</button>
        <button id="save-btn" style="display:none;">저장하기</button>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('#edit-btn').on('click', function() {
        $('#main-content').summernote({
            focus: true,
            height: 300,
            callbacks: {
                onImageUpload: function(files) {
                    // chanlan/pages/ajax_upload_image.php 로직 참고
                    // 해당 파일을 sHotel 프로젝트에도 만들어야 함
                }
            }
        });
        $('#edit-btn').hide();
        $('#save-btn').show();
    });

    $('#save-btn').on('click', function() {
        var markup = $('#main-content').summernote('code');
        $('#main-content').summernote('destroy');
        $('#save-btn').hide();
        $('#edit-btn').show();

        // AJAX로 콘텐츠 저장 (chanlan/pages/ajax_save_page.php 참고)
        $.ajax({
            url: 'ajax_save_page.php', // 이 파일도 생성해야 함
            method: 'POST',
            data: {
                page_name: 'main',
                content: markup
            },
            success: function(response) {
                alert('저장되었습니다.');
            }
        });
    });
});
</script>