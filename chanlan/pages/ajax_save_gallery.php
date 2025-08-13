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
$thumbnail_path = null;

// 1. 썸네일 파일이 직접 업로드되었는지 확인
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['thumbnail'];
    $uploadDir = '../uploads/gallery/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'thumb-' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $thumbnail_path = '../uploads/gallery/' . $newFileName;
    }
}

// 2. 업로드된 썸네일이 없다면, 본문 내용에서 첫 번째 이미지 추출
if (empty($thumbnail_path)) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    if (isset($matches[1])) {
        $thumbnail_path = $matches[1];
    }
}

$password_hash = null;
if ($is_private && !empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

if ($post_id > 0) {
    // 기존 썸네일 경로 유지를 위한 로직 추가
    if (empty($thumbnail_path)) {
        $stmt_thumb = $mysqli->prepare("SELECT thumbnail FROM chan_gallery WHERE id = ?");
        $stmt_thumb->bind_param("i", $post_id);
        $stmt_thumb->execute();
        $thumbnail_path = $stmt_thumb->get_result()->fetch_assoc()['thumbnail'];
        $stmt_thumb->close();
    }
    
    if ($is_private && !empty($password_hash)) {
        $stmt = $mysqli->prepare("UPDATE chan_gallery SET title=?, content=?, thumbnail=?, is_private=?, password_hash=? WHERE id=?");
        $stmt->bind_param("sssisi", $title, $content, $thumbnail_path, $is_private, $password_hash, $post_id);
    } else {
        $stmt = $mysqli->prepare("UPDATE chan_gallery SET title=?, content=?, thumbnail=?, is_private=? WHERE id=?");
        $stmt->bind_param("sssii", $title, $content, $thumbnail_path, $is_private, $post_id);
    }
} else {
    $stmt = $mysqli->prepare("INSERT INTO chan_gallery (gallery_type, title, content, thumbnail, is_private, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $gallery_type, $title, $content, $thumbnail_path, $is_private, $password_hash);
}

if ($stmt->execute()) {
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'redirect_url' => "#/gallery_view?id=" . $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
$stmt->close();
?>