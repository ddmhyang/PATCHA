<?php
// /includes/db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'ddmhyang'); // 본인 DB 아이디
define('DB_PASS', 'Rkwhr1027hyun!'); // 본인 DB 비밀번호
define('DB_NAME', 'ddmhyang');     // 위에서 생성한 테이블이 있는 DB 이름

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    error_log("MySQL 연결 실패: " . $mysqli->connect_error);
    die("데이터베이스 연결에 실패했습니다.");
}

$mysqli->set_charset("utf8mb4");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>