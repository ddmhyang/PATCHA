<?php
session_start();
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '아이디와 비밀번호를 모두 입력해주세요.']);
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        echo json_encode(['status' => 'success', 'message' => '로그인 성공']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '비밀번호가 일치하지 않습니다.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '존재하지 않는 계정입니다.']);
}

$stmt->close();
$mysqli->close();
?>