<?php
// /pages/timeline_save.php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰 오류입니다.']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$year = $_POST['year'] ?? '-';
$desc = $_POST['full_description'] ?? '';
$thumbnail_path = $_POST['existing_thumbnail'] ?? '';

// 썸네일 처리 로직
if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] == 0) {
    $uploadDir = '../uploads/thumbnails/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
    $file = $_FILES['thumbnail_file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'thumb_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $thumbnail_path = 'uploads/thumbnails/' . $newFileName;
    }
}

// DB 저장
if ($id > 0) { // 수정
    $stmt = $mysqli->prepare("UPDATE eden_timeline SET year = ?, thumbnail = ?, full_description = ? WHERE id = ?");
    $stmt->bind_param("sssi", $year, $thumbnail_path, $desc, $id);
} else { // 새로 추가
    $max_order_res = $mysqli->query("SELECT MAX(sort_order) as max_order FROM eden_timeline");
    $new_order = ($max_order_res->fetch_assoc()['max_order'] ?? 0) + 10;
    $stmt = $mysqli->prepare("INSERT INTO eden_timeline (year, thumbnail, full_description, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $year, $thumbnail_path, $desc, $new_order);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '타임라인이 저장되었습니다.', 'redirect_url' => '#/timeline']);
} else {
    echo json_encode(['success' => false, 'message' => 'DB 저장 실패: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>