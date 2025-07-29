<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
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

if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
    $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
        $thumbnail_path = 'uploads/' . $fileName;
    }
}

if ($id) {
    $params = [$title, $content, $writer_name, $kpc_name, $pc_name, $trpg_rule];
    $types = "ssssss";

    if ($thumbnail_path) {
        $sql = "UPDATE posts SET title=?, content=?, writer_name=?, kpc_name=?, pc_name=?, trpg_rule=?, thumbnail_path=? WHERE id=?";
        $params[] = $thumbnail_path;
        $types .= "s";
    } else {
        $sql = "UPDATE posts SET title=?, content=?, writer_name=?, kpc_name=?, pc_name=?, trpg_rule=? WHERE id=?";
    }
    $params[] = $id;
    $types .= "i";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);

} else {
    if (empty($thumbnail_path)) {
        preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
        $thumbnail_path = $matches[1] ?? null;
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
exit; 

?>