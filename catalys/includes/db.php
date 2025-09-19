<?php
// 데이터베이스 연결 설정
$db_host = 'localhost'; // DB 호스트
$db_user = 'yyamhyang';      // DB 사용자명
$db_pass = 'RKwhr1027hyun!';          // DB 비밀번호
$db_name = 'yyamhyang'; // DB 이름

// 데이터베이스 연결
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// 연결 오류 확인
if (mysqli_connect_errno()) {
    die('데이터베이스 연결 실패: ' . mysqli_connect_error());
}

// 문자 인코딩 설정
mysqli_set_charset($conn, "utf8");

// 세션 시작
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>