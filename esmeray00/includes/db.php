<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'esmeray00');
define('DB_PASS', 'Meramera4791!');
define('DB_NAME', 'esmeray00');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die("DB 연결 실패: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8mb4");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>