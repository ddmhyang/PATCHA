<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'bloowen');
define('DB_PASS', 'h94911213!'); 
define('DB_NAME', 'bloowen');

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