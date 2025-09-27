<?php
// ===== 디버깅을 위한 에러 메시지 표시 설정 =====
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ==========================================

// 1. 세션 시작
session_start();

// 2. DB 연결 시도
// 에러 발생 시 즉시 중단하고 메시지를 보여주도록 '@' 연산자를 제거합니다.
require_once __DIR__ . '/includes/db.php';
echo "--- 1. ajax_login.php 파일 실행됨. DB 연결 성공. ---\n";

// 3. 전송된 데이터 확인
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
echo "--- 2. 전송받은 아이디: " . htmlspecialchars($username) . " ---\n";
echo "--- 3. 전송받은 비밀번호: " . htmlspecialchars($password) . " ---\n";

if (empty($username) || empty($password)) {
    echo "!!! 오류: 아이디나 비밀번호가 비어있습니다. !!!\n";
    exit;
}

// 4. SQL 쿼리 준비
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
if ($stmt === false) {
    echo "!!! 오류: SQL 쿼리 준비 실패: " . $mysqli->error . " !!!\n";
    exit;
}
echo "--- 4. SQL 쿼리 준비 성공. ---\n";

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// 5. 쿼리 결과 확인
if ($result->num_rows === 1) {
    echo "--- 5. 일치하는 사용자를 찾음. ---\n";
    $user = $result->fetch_assoc();
    
    // 6. 비밀번호 해시 값 비교
    echo "--- 6. DB에 저장된 해시: " . $user['password'] . " ---\n";
    if (password_verify($password, $user['password'])) {
        echo "--- 7. 비밀번호 일치! 로그인 성공. ---\n";
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        // 최종 성공 시에만 JSON 형식으로 응답
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
    } else {
        echo "!!! 오류: 7. 비밀번호가 일치하지 않습니다. !!!\n";
    }
} else {
    echo "!!! 오류: 5. 일치하는 사용자가 없습니다. !!!\n";
}

$stmt->close();
$mysqli->close();
?>