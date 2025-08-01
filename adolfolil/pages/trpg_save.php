<?php
// 1. 데이터베이스 연결 경로를 수정했습니다: __DIR__ 와 '../' 사이에 슬래시(/)를 추가했습니다.
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    die(json_encode(['success' => false, 'message' => '권한이 없습니다.']));
}

// CSRF 토큰 검증을 추가하여 보안을 강화했습니다.
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die(json_encode(['success' => false, 'message' => '유효하지 않은 요청입니다.']));
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$type = 'trpg';
$title = $_POST['title'];
$content = $_POST['content'];
$writer_name = $_POST['writer_name'];
$kpc_name = $_POST['kpc_name'];
$pc_name = $_POST['pc_name'];
$trpg_rule = $_POST['trpg_rule'];

$thumbnail_path = null;

// (수정 시) 기존 썸네일 경로를 먼저 가져옵니다.
if ($id) {
    $stmt_thumb = $mysqli->prepare("SELECT thumbnail_path FROM posts WHERE id = ?");
    $stmt_thumb->bind_param("i", $id);
    $stmt_thumb->execute();
    $result_thumb = $stmt_thumb->get_result();
    if ($row_thumb = $result_thumb->fetch_assoc()) {
        $thumbnail_path = $row_thumb['thumbnail_path'];
    }
    $stmt_thumb->close();
}

// 새 썸네일 파일이 업로드되었는지 확인합니다.
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/thumbnails/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
    $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
        $thumbnail_path = 'uploads/thumbnails/' . $fileName;
    }
}

if ($id) { // --- 게시물 수정 ---
    $params_ref = []; 

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $sql = "UPDATE posts SET title=?, content=?, writer_name=?, kpc_name=?, pc_name=?, trpg_rule=?, thumbnail_path=? WHERE id=?";
        $types = "sssssssi";
        $params = [$title, $content, $writer_name, $kpc_name, $pc_name, $trpg_rule, $thumbnail_path, $id];
    } else {
        $sql = "UPDATE posts SET title=?, content=?, writer_name=?, kpc_name=?, pc_name=?, trpg_rule=? WHERE id=?";
        $types = "ssssssi";
        $params = [$title, $content, $writer_name, $kpc_name, $pc_name, $trpg_rule, $id];
    }

    $stmt = $mysqli->prepare($sql);
    
    // 2. PHP 버전 호환성을 위한 코드로 수정했습니다.
    $bind_params = array_merge([$types], $params);
    foreach($bind_params as $key => $value) {
        $params_ref[$key] = &$bind_params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $params_ref);

} else { // --- 새 게시물 작성 ---
    if (empty($thumbnail_path)) {
        preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
        $thumbnail_path = isset($matches[1]) ? str_replace('../', '', $matches[1]) : null;
    }

    $sql = "INSERT INTO posts (type, title, content, writer_name, kpc_name, pc_name, trpg_rule, thumbnail_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssssssss", $type, $title, $content, $writer_name, $kpc_name, $pc_name, $trpg_rule, $thumbnail_path);
}

if ($stmt->execute()) {
    $new_id = $id ? $id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'message' => '성공적으로 저장되었습니다.', 'redirect_url' => "#/trpg_view?id={$new_id}"]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}

$stmt->close();
?>