<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

$writer = $_POST['writer'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($writer) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '이름과 내용을 모두 입력해주세요.']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO home_note (writer, content) VALUES (?, ?)");
$stmt->bind_param("ss", $writer, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '방명록 작성에 실패했습니다.']);
}
$stmt->close();
?>