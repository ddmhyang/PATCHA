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
$view_type = $_POST['view_type'] ?? '';

if ($id > 0 && !empty($view_type)) {
    $stmt = $mysqli->prepare("UPDATE home2_timeline_positions SET position_y = ?, side = ? WHERE timeline_item_id = ? AND timeline_view = ?");
    $stmt->bind_param("isis", $position_y, $side, $id, $view_type);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '위치 업데이트 실패: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    // 클라이언트에서 전송된 데이터를 로그로 남겨 디버깅에 활용할 수 있습니다.
    error_log("Invalid reorder request: ID=$id, ViewType=$view_type");
    echo json_encode(['success' => false, 'message' => '잘못된 ID 또는 view 타입입니다.']);
}
?>