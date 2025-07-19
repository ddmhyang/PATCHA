<?php
// --- 파일 경로: /pages/main_content.php (최종 수정본) ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$page_name = 'main_content';

// DB에서 콘텐츠 불러오기
$stmt = $mysqli->prepare("SELECT content FROM pages WHERE slug = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다.</p>';
$stmt->close();
?>

<div class="page-container" data-page-name="<?php echo $page_name; ?>">
    <?php if ($is_admin): ?>
        <div id="view-mode">
            <div class="admin-buttons"><button type="button" class="edit-btn">수정하기</button></div>
            <div class="content-display"><?php echo $page_content; ?></div>
        </div>
        <div id="edit-mode" style="display: none;">
            <form class="edit-form" action="../actions/ajax_save_page.php" method="post">
                <input type="hidden" name="page_name" value="<?php echo $page_name; ?>">
                <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
                <div class="admin-buttons">
                    <button type="submit">저장하기</button>
                    <button type="button" class="cancel-btn">취소</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="content-display"><?php echo $page_content; ?></div>
    <?php endif; ?>
</div>

<?php if ($is_admin): ?>
<script>
$(document).ready(function() {
    var pageContainer = $('.page-container[data-page-name="<?php echo $page_name; ?>"]');
    
    // Summernote 초기화
    pageContainer.find('.summernote').summernote({
        height: 350,
        callbacks: {
            onImageUpload: function(files) {
                // 이미지가 업로드되면 아래 함수를 호출
                uploadSummernoteImage(files[0], $(this));
            }
        }
    });

    // Summernote 이미지 업로드 전용 함수
    function uploadSummernoteImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: '../actions/ajax_upload_image.php',
            type: "POST", data: data,
            contentType: false, processData: false, dataType: 'json',
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

    // 수정/보기 모드 전환 버튼
    pageContainer.on('click', '.edit-btn', function() {
        pageContainer.find('#view-mode').hide();
        pageContainer.find('#edit-mode').show();
    });
    pageContainer.on('click', '.cancel-btn', function() {
        pageContainer.find('#edit-mode').hide();
        pageContainer.find('#view-mode').show();
    });
});
</script>
<?php endif; ?>