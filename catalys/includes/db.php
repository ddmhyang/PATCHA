<?php
$db_host = "localhost";
$db_user = "yyamhyang";
$db_pass = "RKwhr1027hyun!";
$db_name = "yyamhyang";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>