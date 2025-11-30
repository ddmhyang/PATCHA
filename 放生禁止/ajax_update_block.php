<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false]);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$content = $_POST['content'] ?? ''; 

if ($id > 0) {
    
    $stmt = $mysqli->prepare("UPDATE post_blocks SET content = ? WHERE id = ?");
    $stmt->bind_param("si", $content, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
}
?>