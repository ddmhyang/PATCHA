<?php
// /pages/ajax_save_page.php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'CSRF 토큰 오류. 페이지를 새로고침 하세요.']);
        exit;
    }
    
    if (isset($_POST['page_name']) && isset($_POST['content'])) {
        $page_name = $_POST['page_name'];
        $content = $_POST['content'];

        $stmt = $mysqli->prepare("INSERT INTO eden_pages_content (page_name, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = ?");
        $stmt->bind_param("sss", $page_name, $content, $content);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB 저장 실패: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방식입니다.']);
}
$mysqli->close();
?>