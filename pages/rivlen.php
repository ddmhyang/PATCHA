<?php
// /pages/eden.php
// main.php에서 이미 db.php를 require 했으므로 여기서는 생략합니다.

$page_name = 'rivlen'; // 이 페이지의 고유 이름

// DB에서 컨텐츠 불러오기
$stmt = $mysqli->prepare("SELECT content FROM eden_pages_content WHERE page_name = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '콘텐츠가 없습니다.';
$stmt->close();
?>

<div class="page-container">
    <?php if ($is_admin): // $is_admin 변수는 main.php에서 설정됨 ?>
        <form id="edit-form" action="ajax_save_page.php" method="post">
            <input type="hidden" name="page_name" value="<?php echo $page_name; ?>">
            <textarea id="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장하기</button>
        </form>
    <?php else: ?>
        <div class="content-display">
            <?php echo $page_content; ?>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Summernote 초기화
    $('#summernote').summernote({
        height: 300,
        callbacks: {
            // Summernote에서 이미지 업로드 시 처리
            onImageUpload: function(files) {
                // ajax_upload_image.php를 통해 서버에 이미지 업로드
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
                // 업로드 성공 시 에디터에 이미지 삽입
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
                    // 필요하다면 페이지 새로고침
                    // location.reload();
                } else {
                    alert('저장 실패: ' + response.message);
                }
            },
            dataType: 'json'
        });
    });
});
</script>