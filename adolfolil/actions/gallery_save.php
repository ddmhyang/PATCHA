<?php
// --- 파일 경로: /actions/gallery_save.php (최종 수정본) ---
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { die(json_encode(['success' => false, 'message' => '권한이 없습니다.'])); }
// CSRF 토큰 검사는 main.js에서 보내는 방식에 맞춰 수정 필요 (일단 생략)

$title = $_POST['title'];
$content = $_POST['content'];
$type = $_POST['type'];
$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$author_id = $_SESSION['user_id'];
$thumbnail_path = null;

// 1. 썸네일 직접 업로드 처리
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/thumbnails/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
    $targetFile = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
        $thumbnail_path = 'uploads/thumbnails/' . $fileName;
    }
}

// 2. 직접 업로드한 썸네일이 없을 경우, 본문에서 첫 번째 이미지 추출
if (empty($thumbnail_path)) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    $thumbnail_path = $matches[1] ?? null;
}

// 3. DB 저장 로직
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
    echo json_encode(['success' => true, 'redirect_url' => "#/{$type}_view?id={$new_id}"]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>