<?php
session_start();
require_once __DIR__ . '/includes/db.php'; // DB 접속 방법을 알려주는 코드 추가!

if (!isset($_GET['id'])) {
    echo "<p>잘못된 접근입니다.</p>";
    exit;
}

$post_id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT * FROM gallery WHERE id = ?"); // 이제 $mysqli를 인식합니다.
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>존재하지 않는 게시글입니다.</p>";
    exit;
}
$post = $result->fetch_assoc();
?>

<div class="view-container">
    <div class="gallery-content2">
        <div class="view-header">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <p class="view-meta">작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></p>
        </div>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="index.php?page=gallery_edit&id=<?php echo $post['id']; ?>" class="btn-edit">수정</a>
            <button id="delete-btn" class="btn-delete" data-id="<?php echo $post['id']; ?>">삭제</button>
        <?php endif; ?>
        
        <div class="view-content">
            <?php echo $post['content']; ?>
        </div>

        <div class="view-actions">
            <a href="index.php?page=gallery" class="btn-list">목록으로</a>
        </div>
    </div>
</div>

<script>
    $('#delete-btn').on('click', function() {
        if (confirm('정말로 이 게시글을 삭제하시겠습니까?')) {
            let postId = $(this).data('id');
            $.ajax({
                url: 'ajax_delete_gallery.php',
                method: 'POST',
                data: { id: postId },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        alert('삭제되었습니다.');
                        // 'navigate' 라는 이름으로 방송을 보냅니다.
                        $(document).trigger('navigate', { url: 'index.php?page=gallery' });
                    } else {
                        alert('삭제 실패: ' + response.message);
                    }
                }
            });
        }
    });
</script>