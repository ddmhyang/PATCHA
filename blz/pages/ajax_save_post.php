<?php
session_start();
if (!isset($_SESSION['blz_logged_in']) || $_SESSION['blz_logged_in'] !== true) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        die('권한이 없습니다.');
    } else {
        header('Location: ../index.php');
    }
    exit;
}
?>
<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '오류: 권한이 없습니다.']);
    exit;
}

if (!isset($_POST['type'], $_POST['title'], $_POST['content'])) {
    echo json_encode(['success' => false, 'message' => '오류: 필수 데이터(type, title, content)가 누락되었습니다.']);
    exit;
}

$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = $_POST['type'];
$title = $_POST['title'];
$content = $_POST['content'];

$thumbnail_path = $_POST['thumbnail'] ?? null;
if (empty($thumbnail_path)) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    $thumbnail_path = $matches[1] ?? null;
}

try {
    if ($post_id > 0) {
        $stmt = $mysqli->prepare("UPDATE blz_posts SET title = ?, content = ?, thumbnail_path = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $thumbnail_path, $post_id);
    } else { 
        $stmt = $mysqli->prepare("INSERT INTO blz_posts (type, title, content, thumbnail_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $type, $title, $content, $thumbnail_path);
    }

    if ($stmt->execute()) {
        $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
        echo json_encode(['success' => true, 'redirect_id' => $new_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'SQL 실행 실패: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '서버 오류: ' . $e->getMessage()]);
}
?>