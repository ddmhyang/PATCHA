<?php
// /pages/timeline_reorder.php (신규)
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰 오류입니다.']);
    exit;
}

$order_data = $_POST['order'] ?? [];
if (empty($order_data)) {
    echo json_encode(['success' => false, 'message' => '순서 데이터가 없습니다.']);
    exit;
}

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("UPDATE eden_timeline SET sort_order = ? WHERE id = ?");
    foreach ($order_data as $item) {
        $sort_order = (int)$item['sort_order'];
        $id = (int)$item['id'];
        $stmt->bind_param("ii", $sort_order, $id);
        $stmt->execute();
    }
    $stmt->close();
    $mysqli->commit();
    echo json_encode(['success' => true]);
} catch (mysqli_sql_exception $exception) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
$mysqli->close();
?>