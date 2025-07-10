<?php
// /pages/gallery_save.php (수정)
require_once '../includes/db.php';

// 응답 형식을 JSON으로 변경
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']);
        exit;
    }

    $title = $_POST['title'];
    $content = $_POST['content'];
    $gallery_type = $_POST['gallery_type'];
    $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $thumbnail = $_POST['thumbnail'] ?? null;

    if (empty($thumbnail)) {
        preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
        $thumbnail = $matches[1] ?? null;
    }

    if ($post_id > 0) {
        $stmt = $mysqli->prepare("UPDATE eden_gallery SET title = ?, content = ?, gallery_type = ?, thumbnail = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $content, $gallery_type, $thumbnail, $post_id);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO eden_gallery (title, content, gallery_type, thumbnail) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $content, $gallery_type, $thumbnail);
    }

    if ($stmt->execute()) {
        $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
        // [수정] header() 대신 JSON 응답을 보냅니다.
        echo json_encode(['success' => true, 'redirect_url' => '#/gallery_view?id=' . $new_id]);
    } else {
        echo json_encode(['success' => false, 'message' => '저장에 실패했습니다: ' . $stmt->error]);
    }
    $stmt->close();
}
$mysqli->close();
?>