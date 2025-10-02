<?php
require_once '../includes/db.php';
// 이 파일의 응답은 JSON 형식임을 브라우저에 알립니다.
header('Content-Type: application/json');

// 응답으로 보낼 배열을 초기화합니다.
$response = ['success' => false, 'message' => ''];

// login.php 폼에서 POST 방식으로 전송된 username과 password를 받습니다.
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $response['message'] = '아이디와 비밀번호를 모두 입력해주세요.';
} else {
    // DB에서 입력된 username과 일치하는 관리자 정보를 찾습니다.
    $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // 사용자가 존재하면
    if ($user = $result->fetch_assoc()) {
        // password_verify() 함수로 입력된 비밀번호와 DB에 저장된 암호화된 해시를 안전하게 비교합니다.
        if (password_verify($password, $user['password_hash'])) {
            // 일치하면, 세션에 관리자 로그인 상태를 기록하고 성공 응답을 설정합니다.
            $_SESSION['admin_logged_in'] = true;
            $response['success'] = true;
        } else {
            $response['message'] = '아이디 또는 비밀번호가 잘못되었습니다.';
        }
    } else {
        $response['message'] = '존재하지 않는 사용자입니다.';
    }
    $stmt->close();
}

// 최종적으로 설정된 $response 배열을 JSON 형식의 문자열로 변환하여 출력합니다.
echo json_encode($response);
?>