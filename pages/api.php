<?php
// /pages/api.php
require_once '../includes/db.php';

// 관리자 로그인 상태 확인
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// CSRF 토큰 전달
$csrf_token = $_SESSION['csrf_token'];

// 허용된 페이지 목록 (home 제거)
$allowed_pages = [
    'page_view',
    'gallery1', 'gallery2', 'gallery_etc', 'gallery_view', 'gallery_upload', 'gallery_edit',
    'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit',
    'search', 'timeline'
];

// 기본 페이지를 page_view로 설정
$page = $_GET['page'] ?? 'page_view';

if (in_array($page, $allowed_pages)) {
    // 출력 버퍼링 시작
    ob_start();

    $page_file = $page . '.php';
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        echo '<h1>페이지 파일을 찾을 수 없습니다.</h1>';
    }

    // 버퍼의 내용을 가져오고 버퍼를 정리
    $content = ob_get_clean();
    echo $content;
} else {
    http_response_code(404);
    echo '<h1>페이지를 찾을 수 없습니다.</h1>';
}
?>