<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// 삭제할 게시물의 ID를 POST 데이터로 받습니다.
$post_id = intval($_POST['id'] ?? 0);
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 게시물 ID입니다.']);
    exit;
}

// 해당 ID를 가진 레코드를 'chan_gallery' 테이블에서 DELETE하는 쿼리를 준비하고 실행합니다.
$stmt = $mysqli->prepare("DELETE FROM chan_gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 삭제에 실패했습니다.']);
}
$stmt->close();
?>