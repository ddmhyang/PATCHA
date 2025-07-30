<?php
// /blz/ajax_save_page.php (신규 파일)
require_once 'includes/db.php';
header('Content-Type: application/json');

// 1. 관리자 권한 확인
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// 2. POST 데이터 확인
if (!isset($_POST['slug']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    exit;
}

$slug = $_POST['slug'];
$content = $_POST['content'];

// 3. 데이터베이스에 저장 (INSERT ... ON DUPLICATE KEY UPDATE 사용)
// slug가 이미 있으면 content를 업데이트하고, 없으면 새로 추가합니다.
$stmt = $mysqli->prepare("INSERT INTO blz_pages (slug, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = ?");
$stmt->bind_param("sss", $slug, $content, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장에 실패했습니다: ' . $stmt->error]);
}

$stmt->close();
?>