<?php
// /includes/db.php
// 데이터베이스 연결 설정
define('DB_HOST', 'localhost');      // 데이터베이스 호스트
define('DB_USER', 'eden0311');       // 데이터베이스 사용자 이름
define('DB_PASS', 'eden9133*7'); // 데이터베이스 비밀번호
define('DB_NAME', 'eden0311');       // 데이터베이스 이름

// MySQLi 객체 지향 방식으로 연결
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 연결 오류 확인
if ($mysqli->connect_error) {
    // 실제 서비스에서는 사용자에게 자세한 오류를 노출하지 않는 것이 좋습니다.
    error_log("MySQL 연결 실패: " . $mysqli->connect_error); // 로그 파일에 기록
    die("데이터베이스 연결에 실패했습니다. 관리자에게 문의하세요."); // 사용자에게 간단한 메시지 표시
}

// 문자셋 설정 (UTF-8)
if (!$mysqli->set_charset("utf8mb4")) {
    error_log("MySQL 문자셋 설정 실패: " . $mysqli->error);
}

// 세션 시작 (모든 페이지에서 세션을 사용하기 위해 여기에 추가)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// CSRF 토큰 생성
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token']; // 다른 파일에서 사용할 수 있도록 변수화
?>