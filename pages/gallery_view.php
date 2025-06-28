<?php
// /pages/gallery_view.php
if (!isset($_GET['id'])) {
    echo "잘못된 접근입니다.";
    exit;
}
$post_id = intval($_GET['id']);

// 게시물 정보 불러오기
$stmt = $mysqli->prepare("SELECT * FROM eden_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "게시물이 존재하지 않습니다.";
    exit;
}
?>
<div class="post-view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">
        작성일: <?php echo $post['created_at']; ?>
        <?php if ($is_admin): ?>
            <a href="main.php?page=gallery_edit&id=<?php echo $post['id']; ?>">수정</a>
            <a href="gallery_delete.php?id=<?php echo $post['id']; ?>&token=<?php echo $csrf_token; ?>" 
                onclick="return confirm('정말 이 게시물을 삭제하시겠습니까?');">삭제</a>
        <?php endif; ?>
    </div>
    <hr>
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    <a href="main.php?page=<?php echo htmlspecialchars($post['gallery_type']); ?>">목록으로</a>
</div>