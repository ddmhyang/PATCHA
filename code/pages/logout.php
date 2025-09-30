<?php
// 세션을 시작합니다.
session_start();
// $_SESSION 배열을 빈 배열로 만들어 모든 세션 변수를 제거합니다.
$_SESSION = array();
// 만약 세션 쿠키를 사용하고 있다면, 만료 시간을 과거로 설정하여 쿠키를 무효화합니다.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
// 서버에 저장된 세션 파일 자체를 파괴합니다.
session_destroy();
// Location 헤더를 전송하여 사용자를 웹사이트의 가장 초기 화면으로 리디렉션시킵니다.
header('Location: ../index.php');
// header 함수 사용 후에는 스크립트 실행을 즉시 중단하는 것이 안전합니다.
exit;
?>