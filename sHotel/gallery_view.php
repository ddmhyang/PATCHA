<?php
if (!isset($_GET['id'])) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='index.php?page=gallery';</script>";
    exit;
}

$post_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('존재하지 않는 게시글입니다.'); location.href='index.php?page=gallery';</script>";
    exit;
}
$post = $result->fetch_assoc();
?>

<div class="view-container">
    <div class="view-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="view-meta">작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></p>
    </div>
    
    <div class="view-content">
        <?php echo $post['content']; ?>
    </div>

    <div class="view-actions">
        <a href="index.php?page=gallery" class="btn-list">목록으로</a>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="index.php?page=gallery_edit&id=<?php echo $post['id']; ?>" class="btn-edit">수정</a>
            <button id="delete-btn" class="btn-delete" data-id="<?php echo $post['id']; ?>">삭제</button>
        <?php endif; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#delete-btn').on('click', function() {
        if (confirm('정말로 이 게시글을 삭제하시겠습니까?')) {
            let postId = $(this).data('id');
            $.ajax({
                url: 'ajax_delete_gallery.php',
                method: 'POST',
                data: { id: postId },
                success: function(response) {
                    alert('삭제되었습니다.');
                    location.href = 'index.php?page=gallery';
                }
            });
        }
    });
});
</script>