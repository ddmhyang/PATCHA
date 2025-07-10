<?php
// /pages/timeline_delete.php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['token']) || !hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 ID입니다.']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM eden_timeline WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'redirect_url' => '#/timeline']);
} else {
    echo json_encode(['success' => false, 'message' => '삭제에 실패했습니다: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>