<?php
// /blz/ajax_delete_post.php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$post_id = intval($_POST['id'] ?? 0);
if ($post_id > 0) {
    $stmt = $mysqli->prepare("DELETE FROM blz_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '삭제에 실패했습니다.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 ID입니다.']);
}
?>