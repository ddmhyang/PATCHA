<?php
// --- 파일 경로: /pages/main_content.php ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$page_name = 'main_content'; // 이 페이지의 고유 이름

// DB에서 'main_content'에 해당하는 내용을 불러옵니다.
$stmt = $mysqli->prepare("SELECT content FROM pages WHERE slug = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '<p>이곳은 메인 페이지입니다. 관리자로 로그인하면 내용을 수정할 수 있습니다.</p>';
$stmt->close();
?>

<div class="page-container">
    <?php if ($is_admin): ?>
        <form class="edit-form" action="actions/ajax_save_page.php" method="post">
            <input type="hidden" name="page_name" value="<?php echo $page_name; ?>">
            <textarea class="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
            <button type="submit">저장하기</button>
        </form>
    <?php else: ?>
        <div class="content-display">
            <?php echo $page_content; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($is_admin): ?>
<script>
    // 이 페이지가 로드될 때 Summernote를 활성화합니다.
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 350,
            callbacks: {
                onImageUpload: function(files) {
                    uploadImage(files[0], $(this));
                }
            }
        });
    });
</script>
<?php endif; ?>