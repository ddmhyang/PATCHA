<?php
// /pages/eden.php

$page_name = 'white'; 

$stmt = $mysqli->prepare("SELECT content FROM eden_pages_content WHERE page_name = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '콘텐츠가 없습니다.';
$stmt->close();
?>

<div class="page-container">
    <?php if ($is_admin): ?>
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
    $(document).ready(function () {

        $('#summernote').summernote({
            height: 300,
            callbacks: {

                onImageUpload: function (files) {

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
                success: function (url) {

                    editor.summernote('insertImage', url);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error(textStatus + " " + errorThrown);
                }
            });
        }

        $('#edit-form').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function (response) {
                    if (response.success) {
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