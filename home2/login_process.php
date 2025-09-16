<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'error' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['error'] = '아이디와 비밀번호를 모두 입력해주세요.';
        echo json_encode($response);
        exit;
    }

    $stmt = $mysqli->prepare("SELECT id, password_hash FROM home2_admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $response['success'] = true;
        } else {
            $response['error'] = '아이디 또는 비밀번호가 잘못되었습니다.';
        }
    } else {
        $response['error'] = '아이디 또는 비밀번호가 잘못되었습니다.';
    }
} else {
    $response['error'] = '잘못된 접근입니다.';
}

echo json_encode($response);
exit;