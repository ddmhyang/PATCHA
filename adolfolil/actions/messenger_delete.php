<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '잘못된 접근 방식입니다.']);
    exit;
}
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']);
    exit;
}

$message_id = intval($_POST['id'] ?? 0);
if ($message_id <= 0) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 메시지 ID입니다.']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM messages WHERE id = ?");
$stmt->bind_param("i", $message_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '메시지 삭제에 실패했습니다: ' . $stmt->error]);
}

$stmt->close();
exit;

?>