<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$position_y = intval($_POST['position_y'] ?? 0);
$side = $_POST['side'] ?? 'left';
$view_type = $_POST['view_type'] ?? ''; // 어떤 타임라인인지 받아옴

if ($id > 0 && !empty($view_type)) {
    $stmt = $mysqli->prepare("UPDATE home2_timeline_positions SET position_y = ?, side = ? WHERE timeline_item_id = ? AND timeline_view = ?");
    $stmt->bind_param("isis", $position_y, $side, $id, $view_type);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '위치 업데이트 실패']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 ID 또는 view 타입입니다.']);
}
?>