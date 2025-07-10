<?php
// /pages/gallery_delete.php (수정)
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// [수정] 보안을 위해 GET 대신 POST 방식으로 토큰과 ID를 받습니다.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['token']) || !hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']);
    exit;
}

$post_id = intval($_POST['id'] ?? 0);
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => '유효하지 않은 게시물 ID입니다.']);
    exit;
}

$stmt_select = $mysqli->prepare("SELECT gallery_type FROM eden_gallery WHERE id = ?");
$stmt_select->bind_param("i", $post_id);
$stmt_select->execute();
$post = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$post) {
    echo json_encode(['success' => false, 'message' => '삭제할 게시물이 존재하지 않습니다.']);
    exit;
}
$gallery_type = $post['gallery_type'];

$stmt_delete = $mysqli->prepare("DELETE FROM eden_gallery WHERE id = ?");
$stmt_delete->bind_param("i", $post_id);

if ($stmt_delete->execute()) {
    $redirect_url = ($gallery_type === 'trpg') ? '#/trpg' : '#/' . $gallery_type;
    echo json_encode(['success' => true, 'redirect_url' => $redirect_url]);
} else {
    echo json_encode(['success' => false, 'message' => '게시물 삭제에 실패했습니다: ' . $stmt_delete->error]);
}

$stmt_delete->close();
$mysqli->close();
?>