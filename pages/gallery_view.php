<?php
// /pages/gallery_view.php
if (!isset($_GET['id'])) {
    echo "잘못된 접근입니다.";
    exit;
}
$post_id = intval($_GET['id']);


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
        작성일:
        <?php echo $post['created_at']; ?>
        <?php if ($is_admin): ?>
        <a
            href="#/gallery_edit?id=<?php echo $post['id']; ?>"
            class="btn-action">수정</a>
        <a
            href="gallery_delete.php?id=<?php echo $post['id']; ?>&token=<?php echo $csrf_token; ?>"
            onclick="return confirm('정말 이 게시물을 삭제하시겠습니까?');"
            class="btn-action btn-delete">삭제</a>
        <?php endif; ?>
    </div>
    <hr>
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    <div class="post-actions">
        <a href="#/<?php echo htmlspecialchars($post['gallery_type']); ?>" class="btn-back-to-list">목록으로</a>
    </div>
</div>

<style>
   
    .content {
        position: absolute !important;
        top: 220px;
        left: 50%;
        transform: translateX(-50%);
        width: 1250px;
        height: 605px;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 0;
        box-sizing: border-box;
        overflow-x: hidden;
        overflow-y: scroll;
    }

    .content::-webkit-scrollbar {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background-color: #555;
        border-radius: 4px;
    }
    .content::-webkit-scrollbar-track {
        background-color: #333;
    }

   
    .post-view-container {
        width: 1170px;
       
        margin: 40px;
    }

    .post-view-container h1 {
        text-align: center;
        color: rgb(255, 255, 255);
       
        font-family: 'Fre9';
        font-size: 40px;
        margin-top: 45px;
        margin-bottom: 15px;
    }

    .post-meta {
        text-align: right;
        color: rgba(255, 255, 255, 0.7);
        font-family: 'Fre3';
        font-size: 16px;
        margin-bottom: 25px;
        margin-right: 15px;
    }

    .btn-action {
        margin-left: 10px;
        padding: 12px 25px;
        border-radius: 5px;
        text-decoration: none;
        font-family: 'Fre9';
        font-size: 16px;
        color: black;
        background-color: rgb(255, 255, 255);
        transition: background-color 0.3s ease;
        margin-right: 10px;
    }

    .btn-action.btn-delete {
        background-color: rgb(0, 0, 0);
        color: white;
    }

    .btn-back-to-list {
        display: block;
        width: fit-content;
        margin: 30px auto 0;
        padding: 12px 35px;
        background-color: #ffffff;
        color: black;
        border-radius: 5px;
        text-decoration: none;
        font-size: 20px;
        font-family: 'Fre9';
        text-align: center;
        transition: background-color 0.3s ease;
        margin-bottom: 50px;
    }

    .post-view-container hr {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        margin: 20px 0;
    }

    .post-content {
        font-family: 'Fre3', sans-serif;
        font-size: 20px;
        line-height: 1.8;
        color: rgba(255, 255, 255, 0.95);
        min-height: 250px;
        padding: 0 15px;
        border-radius: 8px;
        padding: 20px;
    }
</style>