<?php
session_start();
include_once "includes/db.php";

// 로그인 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '권한이 없습니다.']);
    exit;
}

$id = $_POST['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '삭제에 실패했습니다.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID가 없습니다.']);
}

$conn->close();
?>