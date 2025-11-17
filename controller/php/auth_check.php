<?php
session_start(); // 세션 시작 (이미 시작되었으면 유지)

// 'admin_logged_in' 세션 변수가 없거나 false이면
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // 로그인 페이지로 강제 이동
    header("Location: login.php");
    exit;
}
// 이 파일 다음에 오는 코드는 '로그인한 사용자'만 실행할 수 있습니다.
?>