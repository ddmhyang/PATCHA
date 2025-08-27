<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$post_id = intval($_POST['id'] ?? 0);
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 ID입니다.']);
    exit;
}

// 트랜잭션 시작
$mysqli->begin_transaction();

try {
    // 1. 위치 정보 테이블에서 삭제
    $stmt1 = $mysqli->prepare("DELETE FROM home2_timeline_positions WHERE timeline_item_id = ?");
    $stmt1->bind_param("i", $post_id);
    if (!$stmt1->execute()) throw new Exception($stmt1->error);
    $stmt1->close();

    // 2. 내용 테이블에서 삭제
    $stmt2 = $mysqli->prepare("DELETE FROM home2_timeline WHERE id = ?");
    $stmt2->bind_param("i", $post_id);
    if (!$stmt2->execute()) throw new Exception($stmt2->error);
    $stmt2->close();

    // 모든 쿼리 성공 시 커밋
    $mysqli->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // 오류 발생 시 롤백
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => '삭제 실패: ' . $e->getMessage()]);
}
?>