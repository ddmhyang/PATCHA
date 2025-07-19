<?php
// --- 파일 경로: /pages/adolfo.php ---
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$page_name = 'adolfo'; // 이 페이지의 고유 이름

$stmt = $mysqli->prepare("SELECT content FROM pages WHERE slug = ?");
$stmt->bind_param("s", $page_name);
$stmt->execute();
$result = $stmt->get_result();
$page_content = $result->fetch_assoc()['content'] ?? '<h1>Adolfo Spinelli</h1><p>프로필을 입력하세요.</p>';
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
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 350,
            callbacks: { onImageUpload: function(files) { uploadImage(files[0], $(this)); } }
        });
    });
</script>
<?php endif; ?>