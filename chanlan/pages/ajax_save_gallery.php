<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'];
$content = $_POST['content'];
$gallery_type = $_POST['gallery_type'];
$is_private = isset($_POST['is_private']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$thumbnail = null;

preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
if (isset($matches[1])) {
    $thumbnail = $matches[1];
}

// 비밀번호 처리
$password_hash = null;
if ($is_private == 1 && !empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

if ($post_id > 0) { // 수정
    if ($is_private == 1 && !empty($password_hash)) { // 비밀번호 변경
        $stmt = $mysqli->prepare("UPDATE chan_gallery SET title=?, content=?, thumbnail=?, is_private=?, password_hash=? WHERE id=?");
        $stmt->bind_param("sssisi", $title, $content, $thumbnail, $is_private, $password_hash, $post_id);
    } else { // 비밀번호 변경 안 함
        $stmt = $mysqli->prepare("UPDATE chan_gallery SET title=?, content=?, thumbnail=?, is_private=? WHERE id=?");
        $stmt->bind_param("sssii", $title, $content, $thumbnail, $is_private, $post_id);
    }
} else { // 신규 작성
    $stmt = $mysqli->prepare("INSERT INTO chan_gallery (gallery_type, title, content, thumbnail, is_private, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $gallery_type, $title, $content, $thumbnail, $is_private, $password_hash);
}

if ($stmt->execute()) {
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'redirect_url' => "#/gallery_view?id=" . $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
$stmt->close();
?>