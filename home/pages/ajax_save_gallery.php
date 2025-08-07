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
$thumbnail = $_POST['thumbnail'] ?? null;

if (empty($thumbnail)) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    if (isset($matches[1])) {
        $thumbnail_path = parse_url($matches[1], PHP_URL_PATH);
        $thumbnail = $thumbnail_path;
    }
}

if ($post_id > 0) {
    $stmt = $mysqli->prepare("UPDATE home_gallery SET title = ?, content = ?, thumbnail = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $thumbnail, $post_id);
} else {
    $stmt = $mysqli->prepare("INSERT INTO home_gallery (gallery_type, title, content, thumbnail) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $gallery_type, $title, $content, $thumbnail);
}

if ($stmt->execute()) {
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'redirect_url' => "#/gallery_view?id=" . $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
$stmt->close();
?>