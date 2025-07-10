<?php
// /pages/api.php
require_once '../includes/db.php';

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$csrf_token = $_SESSION['csrf_token'];

// 허용된 페이지 목록
$allowed_pages = [
    'page_view',
    'gallery1', 'gallery2', 'gallery_etc', 'gallery_view', 'gallery_upload', 'gallery_edit',
    'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit',
    'search', 'timeline'
];

$page = $_GET['page'] ?? 'page_view';

// 기본값이 eden인 page_view를 위해
if ($page === 'page_view' && !isset($_GET['name'])) {
    $_GET['name'] = 'eden';
}

if (in_array($page, $allowed_pages)) {
    ob_start();
    $page_file = $page . '.php';
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        echo '<h1>페이지 파일을 찾을 수 없습니다.</h1>';
    }
    $content = ob_get_clean();
    echo $content;
} else {
    http_response_code(404);
    echo '<h1>페이지를 찾을 수 없습니다.</h1>';
}
?>