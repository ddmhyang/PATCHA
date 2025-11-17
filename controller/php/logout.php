<?php
session_start(); // 세션을 열고

// 모든 세션 변수를 지움
$_SESSION = array();

// 세션을 파괴
session_destroy();

// 로그인 페이지로 이동
header("Location: login.php");
exit;
?>