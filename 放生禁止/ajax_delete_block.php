<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false]);
    exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id > 0) {
    $mysqli->query("DELETE FROM post_blocks WHERE id = $id");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>