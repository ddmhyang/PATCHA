<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$title = $_POST['title'] ?? '';
$subtitle = $_POST['subtitle'] ?? ''; 
$gallery_type = $_POST['gallery_type'] ?? 'gallery';


if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '잘못된 접근(ID 없음)']);
    exit;
}



$sql = "UPDATE gallery SET title = ?, subtitle = ? WHERE id = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssi", $title, $subtitle, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB 에러: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => '쿼리 준비 실패']);
}
?>