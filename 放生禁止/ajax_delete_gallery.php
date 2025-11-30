<?php 
require_once 'includes/db.php'; 
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한 없음']);
    exit;
}

$post_id = intval($_POST['id'] ?? 0);
if ($post_id > 0) {
    $mysqli->query("DELETE FROM post_blocks WHERE gallery_id = $post_id");
    
    $stmt = $mysqli->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '삭제 실패']);
    }
    $stmt->close();
}
?>