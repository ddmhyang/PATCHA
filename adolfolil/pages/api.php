<?php
// --- 파일 경로: /pages/api.php (신규 생성) ---

require_once '../includes/db.php';

$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$csrf_token = $_SESSION['csrf_token'] ?? '';

// SPA를 통해 로드할 수 있는 페이지 목록
$allowed_pages = [
    'main_content', 'dolfolil', 'adolfo', 'lilian', 'messenger',
    'gallery', 'gallery_view', 'gallery_upload', 'gallery_edit',
    'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit'
];

$page = $_GET['page'] ?? 'main_content'; // 기본 페이지는 main_content

if (in_array($page, $allowed_pages)) {
    $page_file = $page . '.php';
    if (file_exists($page_file)) {
        // ob_start와 ob_get_clean을 사용하여 PHP 파일의 실행 결과를 변수에 담아 출력
        ob_start();
        include $page_file;
        echo ob_get_clean();
    } else {
        http_response_code(404);
        echo '<h2>페이지 파일을 찾을 수 없습니다.</h2>';
    }
} else {
    http_response_code(404);
    echo '<h2>페이지를 찾을 수 없습니다.</h2>';
}
?>