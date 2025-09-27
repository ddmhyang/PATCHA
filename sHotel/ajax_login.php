<?php
session_start();
// DB 연결 파일을 절대 경로로 한번만 포함합니다.
require_once __DIR__ . '/includes/db.php';

// 응답을 JSON 형태로 보내기 위한 헤더 설정
header('Content-Type: application/json');

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// 아이디나 비밀번호가 비어있는 경우
if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => '아이디와 비밀번호를 모두 입력해주세요.']);
    exit;
}

// 데이터베이스에서 사용자 정보 조회
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // 사용자가 존재하는 경우
    $user = $result->fetch_assoc();

    // 비밀번호 검증
    if (password_verify($password, $user['password'])) {
        // 로그인 성공
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        echo json_encode(['status' => 'success']);
    } else {
        // 비밀번호 불일치
        echo json_encode(['status' => 'error', 'message' => '비밀번호가 올바르지 않습니다.']);
    }
} else {
    // 사용자가 존재하지 않는 경우
    echo json_encode(['status' => 'error', 'message' => '존재하지 않는 계정입니다.']);
}

$stmt->close();
$mysqli->close();
?>