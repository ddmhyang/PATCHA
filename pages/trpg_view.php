<?php
// /pages/trpg_view.php

if (!isset($_GET['id'])) {
    echo "잘못된 접근입니다.";
    exit;
}
$post_id = intval($_GET['id']);

$stmt = $mysqli->prepare("SELECT * FROM eden_gallery WHERE id = ? AND gallery_type = 'trpg'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "게시물이 존재하지 않거나 TRPG 게시물이 아닙니다.";
    exit;
}
?>
<div class="trpg-session-view-container">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="post-meta">
        작성일: <?php echo $post['created_at']; ?>
        <?php if ($is_admin): ?> &nbsp;
            <a href="#/trpg_edit?id=<?php echo $post['id']; ?>" class="btn-action">수정</a>
            <a href="#" class="btn-action btn-delete"
            data-id="<?php echo $post['id']; ?>"
            data-token="<?php echo $csrf_token; ?>"
            data-url="gallery_delete.php">삭제</a>
        <?php endif; ?>
    </div>
    <hr>
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    <div class="post-actions">
        <a href="#/trpg" class="btn-back-to-list">목록으로</a>
    </div>
</div>

<style>

.trpg-session-view-container{
    width: 1170px;
    margin-left: 40px;
}
.trpg-session-view-container h1 {
    text-align: center;
    color:rgb(255, 255, 255);
    font-family: 'Fre9';
    font-size: 40px;
    margin-top: 45px;
    margin-bottom: 15px;
}

.trpg-session-view-container .post-meta {
    text-align: right;
    color: rgba(255, 255, 255, 0.7);
    font-family: 'Fre3';
    font-size: 16px;
    margin-bottom: 25px;
    margin-right: 15px;
}

.trpg-session-view-container .post-meta .btn-action {
    margin-left: 10px;
    padding: 12px 25px;
    border-radius: 5px;
    text-decoration: none;
    font-family: 'Fre9';
    font-size: 16px;
    color: black;
    background-color:rgb(255, 255, 255);
    transition: background-color 0.3s ease;
    margin-right: 10px;
}


.trpg-session-view-container .post-meta .btn-delete {
    background-color:rgb(0, 0, 0);
    color: white;
}


.trpg-session-view-container .post-content {
    font-family: 'Fre3', sans-serif;
    font-size: 20px; 
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.95);
    min-height: 250px;
    padding: 0 15px;
    border-radius: 8px;
    padding: 20px;
}


.trpg-session-view-container .btn-back-to-list {
    display: block;
    width: fit-content;
    margin: 30px auto 0 auto;
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

.content {
    position: absolute !important; top: 220px; left: 50%;
    transform: translateX(-50%); width: 1250px; height: 605px;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
    padding: 0; box-sizing: border-box;
    overflow-y: auto;
    overflow-x: hidden;
}

.content::-webkit-scrollbar{ width: 8px; }
.content::-webkit-scrollbar-thumb { background-color: #555; border-radius: 4px; }
.content::-webkit-scrollbar-track { background-color: #333; }

</style>