<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$slug = $_POST['slug'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($slug) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    exit;
}

$stmt = $mysqli->prepare("UPDATE pages SET content = ? WHERE slug = ?");
$stmt->bind_param("ss", $content, $slug);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => '성공적으로 저장되었습니다.',
    ]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장에 실패했습니다.']);
}

$stmt->close();
?>