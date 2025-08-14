<?php
// 1. 세션을 시작합니다.
session_start();

// 2. 세션 배열의 모든 데이터를 비웁니다.
$_SESSION = array();

// 3. 클라이언트에 저장된 세션 쿠키를 만료시킵니다.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. 마지막으로, 세션을 완전히 파괴합니다.
session_destroy();

// 5. 사이트의 초기 화면으로 리디렉션합니다.
header('Location: ../index.php');
exit;
?>