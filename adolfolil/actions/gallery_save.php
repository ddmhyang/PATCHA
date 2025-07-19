<?php
// --- 파일 경로: /actions/gallery_save.php ---
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) { die(json_encode(['success' => false, 'message' => '권한이 없습니다.'])); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { die(json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.'])); }

$title = $_POST['title'];
$content = $_POST['content'];
$type = $_POST['type']; // 'gallery' 또는 'trpg'
$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$author_id = $_SESSION['user_id'];
$thumbnail_path = null;

// 1. 썸네일 이미지 처리
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    // ... (이전 handle_upload.php의 파일 업로드 로직과 동일) ...
    $upload_dir = '../uploads/thumbnails/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
    $file_name = time() . '_' . basename($_FILES['thumbnail']['name']);
    $target_file = $upload_dir . $file_name;
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
        $thumbnail_path = 'uploads/thumbnails/' . $file_name;
    }
}

// 2. 신규 글(INSERT)인지 수정 글(UPDATE)인지 판단
if ($post_id > 0) { // 수정
    if ($thumbnail_path) { // 새 썸네일이 있으면 썸네일도 업데이트
        $stmt = $mysqli->prepare("UPDATE posts SET title = ?, content = ?, thumbnail_path = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $thumbnail_path, $post_id);
    } else { // 썸네일 변경 없으면 제목과 내용만 업데이트
        $stmt = $mysqli->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $post_id);
    }
} else { // 신규
    $stmt = $mysqli->prepare("INSERT INTO posts (author_id, type, title, content, thumbnail_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $author_id, $type, $title, $content, $thumbnail_path);
}

// 3. 쿼리 실행 및 결과 반환
if ($stmt->execute()) {
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'redirect_url' => "#/{$type}_view?id={$new_id}"]);
} else {
    echo json_encode(['success' => false, 'message' => '저장에 실패했습니다: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>