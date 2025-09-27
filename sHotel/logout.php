<?php
// 1. 세션을 시작합니다.
session_start();

// 2. 세션에 저장된 모든 변수를 제거합니다.
session_unset();

// 3. 현재 세션을 완전히 파괴합니다.
session_destroy();

// 4. 모든 작업이 끝나면 로그인 페이지로 리디렉션합니다.
header("Location: index.php?page=login");
exit;
?>