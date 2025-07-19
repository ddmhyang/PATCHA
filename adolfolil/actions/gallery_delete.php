<?php
// --- 파일 경로: /actions/gallery_delete.php ---
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { die(json_encode(['success' => false, 'message' => '권한이 없습니다.'])); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['token'])) { die(json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.'])); }

$post_id = intval($_POST['id'] ?? 0);
if ($post_id <= 0) { die(json_encode(['success' => false, 'message' => '유효하지 않은 ID입니다.'])); }

// (선택사항) 썸네일 파일도 서버에서 삭제하는 로직 추가 가능

$stmt = $mysqli->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
if ($stmt->execute()) {
    // 삭제 후 갤러리 목록으로 이동
    echo json_encode(['success' => true, 'redirect_url' => '#/gallery']);
} else {
    echo json_encode(['success' => false, 'message' => '삭제 실패: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>