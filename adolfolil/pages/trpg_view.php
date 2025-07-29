<?php
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

if (!isset($_GET['id'])) { exit("잘못된 접근입니다."); }
$post_id = intval($_GET['id']);


$stmt = $mysqli->prepare("SELECT * FROM posts WHERE id = ? AND type = 'trpg'");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) { exit("게시물이 존재하지 않습니다."); }
?>

<div class="view-container">
    <div class="trpg-header">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if ($is_admin): ?>
            <div class="admin-buttons">
                <a href="#/trpg_edit?id=<?php echo $post_id; ?>" class="btn btn-secondary">수정</a>
                <button class="delete-btn btn btn-primary" data-id="<?php echo $post_id; ?>" data-type="trpg">삭제</button>
            </div>
        <?php endif; ?>
    </div>

    <div class="view-meta-details">
        <ul>
            <li><strong>W.</strong><?php echo htmlspecialchars($post['writer_name'] ?? '정보 없음'); ?></li>
            <li><strong>KPC:</strong> <?php echo htmlspecialchars($post['kpc_name'] ?? '정보 없음'); ?></li>
            <li><strong>PC:</strong> <?php echo htmlspecialchars($post['pc_name'] ?? '정보 없음'); ?></li>
            <li><strong>사용 룰:</strong> <?php echo htmlspecialchars($post['trpg_rule'] ?? '정보 없음'); ?></li>
        </ul>
    </div>

    <div class="view-meta">
        <span>작성일: <?php echo date("Y-m-d", strtotime($post['created_at'])); ?></span>
    </div>

    <div class="view-content">
        <?php echo $post['content']; ?>
    </div>
    
    <div class="view-footer">
        <a href="#/trpg" class="btn btn-secondary">목록으로</a>
    </div>
</div>

<style>

.view-meta-details {
    background-color: #222;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #1a1a1a;
}
.view-meta-details ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.view-meta-details li {
    font-size: 16px;
    color: #fafafa;
}
.view-meta-details li strong {
    color: #999999;
    margin-right: 8px;
}
</style>