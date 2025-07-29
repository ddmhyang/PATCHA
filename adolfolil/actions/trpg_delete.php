<?php

require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { die(json_encode(['success' => false, 'message' => '권한이 없습니다.'])); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['token'])) { die(json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.'])); }

$post_id = intval($_POST['id'] ?? 0);
if ($post_id <= 0) { die(json_encode(['success' => false, 'message' => '유효하지 않은 ID입니다.'])); }



$stmt = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
if ($stmt->execute()) {
    
    echo json_encode(['success' => true, 'redirect_url' => '#/trpg']);
} else {
    echo json_encode(['success' => false, 'message' => '삭제 실패: ' . $stmt->error]);
}
?>