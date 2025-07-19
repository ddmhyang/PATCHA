<?php
// --- 파일 경로: /pages/lilian.php ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$page_name = 'lilian';

$stmt = $mysqli->prepare("SELECT content FROM pages WHERE slug = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '<p>콘텐츠가 없습니다. 관리자로 로그인하여 내용을 추가하세요.</p>';
$stmt->close();
?>

<div class="page-container" data-page-name="<?php echo $page_name; ?>">
    <?php if ($is_admin): ?>
        <div id="view-mode">
            <div class="admin-buttons"><button type="button" id="edit-btn">수정하기</button></div>
            <div class="content-display"><?php echo $page_content; ?></div>
        </div>

        <div id="edit-mode" style="display: none;">
            <form class="edit-form" action="../actions/ajax_save_page.php" method="post">
                <input type="hidden" name="page_name" value="<?php echo $page_name; ?>">
                <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
                <div class="admin-buttons">
                    <button type="submit">저장하기</button>
                    <button type="button" id="cancel-btn">취소</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="content-display"><?php echo $page_content; ?></div>
    <?php endif; ?>
</div>

<?php if ($is_admin): ?>
<script>
    // 이 페이지가 로드될 때 실행될 스크립트
    $(document).ready(function() {
        var pageContainer = $('.page-container[data-page-name="<?php echo $page_name; ?>"]');
        
        // Summernote 초기화
        pageContainer.find('.summernote').summernote({
            height: 350,
            callbacks: { onImageUpload: function(files) { uploadImage(files[0], $(this)); } }
        });

        // 수정 버튼 클릭 시 모드 전환
        pageContainer.find('#edit-btn').on('click', function() {
            pageContainer.find('#view-mode').hide();
            pageContainer.find('#edit-mode').show();
        });

        // 취소 버튼 클릭 시 모드 전환
        pageContainer.find('#cancel-btn').on('click', function() {
            pageContainer.find('#edit-mode').hide();
            pageContainer.find('#view-mode').show();
        });
    });
</script>
<?php endif; ?>