<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한 없음']);
    exit;
}

$gallery_id = intval($_POST['gallery_id'] ?? 0);
$type = $_POST['type'] ?? 'A'; 
$content = $_POST['content'] ?? '';

if ($gallery_id <= 0) {
    echo json_encode(['success' => false, 'message' => '데이터 부족']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO post_blocks (gallery_id, block_type, content) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $gallery_id, $type, $content);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'id' => $stmt->insert_id, 
        'content' => $content
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
?>