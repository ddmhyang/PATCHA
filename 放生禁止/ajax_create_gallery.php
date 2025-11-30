<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한 없음']);
    exit;
}

$gallery_type = $_POST['gallery_type'] ?? 'gallery';

$stmt = $mysqli->prepare("INSERT INTO gallery (gallery_type, title, created_at) VALUES (?, '새 페이지', NOW())");
$stmt->bind_param("s", $gallery_type);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
?>