<?php
// --- 파일 경로: /actions/ajax_save_page.php ---
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['page_name']) && isset($_POST['content'])) {
        $page_name = $_POST['page_name'];
        $content = $_POST['content'];

        // DB에 page_name이 있으면 UPDATE, 없으면 INSERT 하는 쿼리
        $stmt = $mysqli->prepare("INSERT INTO pages (slug, content) VALUES (?, ?) ON DUPLICATE KEY UPDATE content = ?");
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
}
$mysqli->close();
?>