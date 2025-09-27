<?php
session_start();
include_once "includes/db.php";

// 로그인 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '로그인이 필요합니다.']);
    exit;
}

$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$thumbnail = $_POST['thumbnail'] ?? '';
$id = $_POST['id'] ?? null;

if (empty($title) || empty($content)) {
    echo json_encode(['status' => 'error', 'message' => '제목과 내용은 필수입니다.']);
    exit;
}

if ($id) { // 수정
    $stmt = $conn->prepare("UPDATE gallery SET title = ?, content = ?, thumbnail = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $thumbnail, $id);
} else { // 신규 작성
    $stmt = $conn->prepare("INSERT INTO gallery (title, content, thumbnail) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $thumbnail);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => '저장에 실패했습니다.']);
}

$stmt->close();
$conn->close();
?>