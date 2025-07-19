<?php
// --- 파일 경로: /pages/logout.php ---

// 세션을 사용하기 위해 가장 먼저 호출합니다.
session_start();

// 세션 배열의 모든 데이터를 비웁니다.
$_SESSION = array();

// 세션 쿠키가 있다면, 만료 시간을 과거로 설정하여 삭제합니다.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 마지막으로, 세션을 완전히 파괴합니다.
session_destroy();

// 모든 작업이 끝나면, 사이트의 가장 첫 화면으로 돌려보냅니다.
header('Location: ../index.php');
exit;
?>