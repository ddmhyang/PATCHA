<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

// 비밀번호를 확인할 게시물의 ID와 입력된 비밀번호를 받습니다.
$post_id = intval($_POST['post_id'] ?? 0);
$password = $_POST['password'] ?? '';

if ($post_id <= 0 || empty($password)) {
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
    exit;
}

// 해당 ID를 가진 게시물의 암호화된 비밀번호 해시(password_hash)를 DB에서 가져옵니다.
$stmt = $mysqli->prepare("SELECT password_hash FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

// password_verify() 함수는 사용자가 입력한 평문 비밀번호($password)와
// DB에 저장된 해시값($post['password_hash'])을 비교하여 일치 여부를 boolean으로 반환합니다.
if ($post && password_verify($password, $post['password_hash'])) {
    // 일치하면, 세션에 이 게시물에 대한 접근을 허가하는 정보를 현재 시간(time())으로 기록합니다.
    // gallery_view.php에서 이 세션 값을 확인하여 접근을 허용합니다.
    $_SESSION['post_access'][$post_id] = time();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '비밀번호가 일치하지 않습니다.']);
}
$stmt->close();
?>