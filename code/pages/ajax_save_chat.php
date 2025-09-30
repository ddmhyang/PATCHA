<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// chat.php 폼에서 전송된 캐릭터 이름과 메시지 내용을 받습니다.
$character_name = $_POST['character_name'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($character_name) || empty($message)) {
    echo json_encode(['success' => false, 'message' => '필수 값이 비어있습니다.']);
    exit;
}

// 'chan_chat' 테이블에 새 메시지를 INSERT하는 쿼리를 준비하고 실행합니다.
$stmt = $mysqli->prepare("INSERT INTO chan_chat (character_name, message) VALUES (?, ?)");
$stmt->bind_param("ss", $character_name, $message);

if ($stmt->execute()) {
    // 성공 시, 방금 생성된 메시지의 ID($mysqli->insert_id)를 포함하여 응답합니다.
    echo json_encode(['success' => true, 'id' => $mysqli->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => '메시지 저장에 실패했습니다.']);
}
$stmt->close();
?>