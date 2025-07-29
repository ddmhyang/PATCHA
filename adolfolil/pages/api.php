<?php


require_once '../includes/db.php';

$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$csrf_token = $_SESSION['csrf_token'] ?? '';


$allowed_pages = [
    'main_content', 'dolfolil', 'adolfo', 'lilian', 'messenger',
    'gallery', 'gallery_view', 'gallery_upload', 'gallery_edit',
    'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit'
];

$page = $_GET['page'] ?? 'main_content'; 

if (in_array($page, $allowed_pages)) {
    $page_file = $page . '.php';
    if (file_exists($page_file)) {
        
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