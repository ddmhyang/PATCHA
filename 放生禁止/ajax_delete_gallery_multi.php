<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
    echo json_encode(['success' => false, 'message' => '삭제할 항목이 없습니다.']);
    exit;
}

$ids = $_POST['ids'];
$success_count = 0;

foreach ($ids as $id) {
    $post_id = intval($id);
    if ($post_id > 0) {
        $mysqli->query("DELETE FROM post_blocks WHERE gallery_id = $post_id");
        
        $stmt = $mysqli->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            $success_count++;
        }
        $stmt->close();
    }
}

if ($success_count > 0) {
    echo json_encode(['success' => true, 'count' => $success_count]);
} else {
    echo json_encode(['success' => false, 'message' => '삭제 실패']);
}
?>