<?php

require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { die(json_encode(['success' => false, 'message' => '권한이 없습니다.'])); }


$title = $_POST['title'];
$content = $_POST['content'];
$type = $_POST['type'];
$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$author_id = $_SESSION['user_id'];
$thumbnail_path = null;


if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/thumbnails/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
    $targetFile = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
        $thumbnail_path = 'uploads/thumbnails/' . $fileName;
    }
}


if (empty($thumbnail_path)) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    $thumbnail_path = $matches[1] ?? null;
}


if ($post_id > 0) {
    if ($thumbnail_path) {
        $stmt = $mysqli->prepare("UPDATE posts SET title = ?, content = ?, thumbnail_path = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $thumbnail_path, $post_id);
    } else {
        $stmt = $mysqli->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $post_id);
    }
} else {
    $stmt = $mysqli->prepare("INSERT INTO posts (author_id, type, title, content, thumbnail_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $author_id, $type, $title, $content, $thumbnail_path);
}

if ($stmt->execute()) {
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'message' => '성공적으로 저장되었습니다.', 'redirect_url' => "#/{$type}_view?id={$new_id}"]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
?>