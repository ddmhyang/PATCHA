<?php
// /pages/timeline_detail.php (신규)
if (!isset($mysqli)) {
    // SPA가 아닌 직접 접근을 방지하기 위함
    $id = $_GET['id'] ?? 0;
    header("Location: main.php?page=timeline_detail&id=" . $id);
    exit;
}

$item_id = intval($_GET['id'] ?? 0);
if ($item_id <= 0) {
    echo '<h1>잘못된 접근입니다.</h1>';
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM eden_timeline WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    echo '<h1>항목을 찾을 수 없습니다.</h1>';
    exit;
}
?>
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
        overflow-y: auto;
        overflow-x: hidden;
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

    .detail-container {
        color: white;
        padding: 40px;
        box-sizing: border-box;
        height: 100%;
        overflow-y: auto;
    }
    .detail-container::-webkit-scrollbar {
        width: 0;
    }
    .detail-container h1 {
        font-family: 'Fre7';
        margin-bottom: 20px;
    }
    .detail-container hr {
        border-color: rgba(255,255,255,0.2);
        margin: 20px 0;
    }
    .detail-controls {
        margin-top: 30px;
    }
    .detail-controls .btn-action {
        font-family: 'Fre7';
        background: white;
        color: black;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        text-decoration: none;
        border-radius: 5px;
        margin-right: 10px;
    }
    .detail-controls .btn-delete {
        background: black;
        color: white;
    }
</style>

<div class="detail-container">
    <h1><?php echo htmlspecialchars($item['year']); ?></h1>
    <hr>
    <div class="content-display">
        <?php echo $item['full_description']; ?>
    </div>

    <?php if ($is_admin): ?>
    <div class="detail-controls">
        <a href="#/timeline_edit?id=<?php echo $item['id']; ?>" class="btn-action">수정</a>
        <a
            href="#"
            class="btn-action btn-delete"
            data-id="<?php echo $item['id']; ?>"
            data-token="<?php echo $csrf_token; ?>"
            data-url="timeline_delete.php">삭제</a>
    </div>
    <?php endif; ?>

    <hr>
    <a href="#/timeline" style="color:white;">&laquo; 타임라인으로 돌아가기</a>
</div>