<?php
require_once '../includes/db.php';
$page_slug = 'creamypuff';
//

$stmt = $mysqli->prepare("SELECT content FROM pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$page_content = $stmt->get_result()->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다.</p>';
$stmt->close();
?>

<div class="page-container">
    <div id="view-mode">
        <div class="content-display"><?php echo $page_content; ?></div>
        <?php if ($is_admin): ?><button class="edit-btn">수정하기</button><?php endif; ?>
    </div>
    <?php if ($is_admin): // 아래는 관리자에게만 보이는 숨겨진 수정 영역입니다. ?>
    <div id="edit-mode" style="display:none;">
        <form class="ajax-form" action="ajax_save_page.php" method="post">
            <input type="hidden" name="slug" value="<?php echo $page_slug; ?>">
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장하기</button> 
            <button type="button" class="cancel-btn">취소하기</button>
        </form>
    </div>
    <script>
    $('.edit-btn').click(function() {
        $('#view-mode').hide();
        $('#edit-mode').show();
        $('.summernote').summernote({
            height: 400,
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana', 'Nanum SonPyeonJiCe', 'Kolker Brush'],
            fontNamesIgnoreCheck: ['Nanum SonPyeonJiCe', 'Kolker Brush'],
            focus: true,
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
    });
    $('.cancel-btn').click(function() {
        $('.summernote').summernote('destroy');
        $('#edit-mode').hide();
        $('#view-mode').show();
    });
    </script>
    <?php endif; // 관리자 전용 영역 끝 ?>
</div>