<?php
session_start();
if (!isset($_SESSION['blz_logged_in']) || $_SESSION['blz_logged_in'] !== true) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        die('권한이 없습니다.');
    } else {
        header('Location: ../index.php');
    }
    exit;
}
?>
<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if (!isset($_POST['slug']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    exit;
}

$slug = $_POST['slug'];
$content = $_POST['content'];

$stmt = $mysqli->prepare("INSERT INTO blz_pages (slug, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = ?");
$stmt->bind_param("sss", $slug, $content, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장에 실패했습니다: ' . $stmt->error]);
}

$stmt->close();
?>