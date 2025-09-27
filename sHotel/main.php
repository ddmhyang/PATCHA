<?php
session_start(); // 이 줄을 추가하세요!
require_once __DIR__ . '/includes/db.php';

// DB에서 main 페이지 콘텐츠 가져오기
$sql = "SELECT * FROM pages WHERE page_name = 'main'";
$result = $mysqli->query($sql);
$page_content = "";
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $page_content = $row['content'];
}
?>

<div class="page-container">
    <div id="main-content" class="editable-content">
        <?php echo $page_content; ?>
    </div>
    
    <div class="button-wrapper">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <button id="edit-btn">Edit</button>
            <button id="save-btn" style="display:none;">Save</button>
        <?php endif; ?>
    </div>
</div>

<script>
    // '수정' 버튼 클릭 이벤트
    $('#edit-btn').on('click', function() {
        $('#main-content').summernote({
            focus: true,
            height: 300,
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana', 'HBIOS-SYS', 'Gravitas One'],
            fontNamesIgnoreCheck: ['HBIOS-SYS', 'Gravitas One'],
            callbacks: {
                // ▼▼▼ 여기에 이미지 업로드 로직을 추가했습니다 ▼▼▼
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
                                $('#main-content').summernote('insertImage', response.url);
                            } else {
                                alert('이미지 업로드 실패: ' + response.error);
                            }
                        },
                        error: function() {
                            alert('이미지 업로드 중 서버 통신 오류가 발생했습니다.');
                        }
                    });
                }
            }
        });
        $('#edit-btn').hide();
        $('#save-btn').show();
    });

    // '저장' 버튼 클릭 이벤트
    $('#save-btn').on('click', function() {
        var markup = $('#main-content').summernote('code');
        $('#main-content').summernote('destroy');
        $('#save-btn').hide();
        $('#edit-btn').show();

        $.ajax({
            url: 'ajax_save_page.php',
            method: 'POST',
            data: {
                page_name: 'main',
                content: markup
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alert('저장되었습니다.');
                } else {
                    alert('저장 실패: ' + response.message);
                }
            }
        });
    });
</script>