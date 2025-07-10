<?php
// /pages/gallery_delete.php
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    die("권한이 없습니다.");
}

if (!isset($_GET['token']) || !hash_equals($_SESSION['csrf_token'], $_GET['token'])) {
    die('CSRF 토큰이 유효하지 않습니다.');
}

if (!isset($_GET['id'])) {
    die("잘못된 요청입니다.");
}
$post_id = intval($_GET['id']);
if ($post_id <= 0) {
    die("유효하지 않은 게시물 ID입니다.");
}

$stmt_select = $mysqli->prepare("SELECT gallery_type FROM eden_gallery WHERE id = ?");
$stmt_select->bind_param("i", $post_id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$post = $result->fetch_assoc();
$stmt_select->close();

if (!$post) {
    die("삭제할 게시물이 존재하지 않습니다.");
}
$gallery_type = $post['gallery_type'];

$stmt_delete = $mysqli->prepare("DELETE FROM eden_gallery WHERE id = ?");
$stmt_delete->bind_param("i", $post_id);

if ($stmt_delete->execute()) {
    header("Location: main.php?page=" . urlencode($gallery_type));
    exit;
} else {
    die("게시물 삭제에 실패했습니다: " . $stmt_delete->error);
}

$stmt_delete->close();
$mysqli->close();
?>