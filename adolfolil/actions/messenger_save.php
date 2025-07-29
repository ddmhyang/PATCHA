<?php

require_once '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die(json_encode(['success' => false, 'message' => '잘못된 접근입니다.'])); }

$character = $_POST['character'] ?? '';
$message = $_POST['message'] ?? '';
$author_id = $_SESSION['user_id'] ?? 1; 

if (empty($message) || !in_array($character, ['Adolfo', 'Lilian'])) {
    die(json_encode(['success' => false, 'message' => '내용이 올바르지 않습니다.']));
}

$stmt = $mysqli->prepare("INSERT INTO messages (author_id, character_name, message_text) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $author_id, $character, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'new_id' => $mysqli->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
?>