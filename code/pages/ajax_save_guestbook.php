<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

// 방명록 폼에서 전송된 작성자(writer)와 내용(content)을 받습니다.
$writer = $_POST['writer'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($writer) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '이름과 내용을 모두 입력해주세요.']);
    exit;
}

// 'chan_guestbook' 테이블에 새 방명록 글을 INSERT하는 쿼리를 준비하고 실행합니다.
$stmt = $mysqli->prepare("INSERT INTO chan_guestbook (writer, content) VALUES (?, ?)");
$stmt->bind_param("ss", $writer, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '방명록 작성에 실패했습니다.']);
}
$stmt->close();
?>