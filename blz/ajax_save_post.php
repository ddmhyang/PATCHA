<?php
// /blz/ajax_save_post.php (최종 수정본)
require_once 'includes/db.php';
header('Content-Type: application/json');

// 관리자 권한 확인
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '오류: 권한이 없습니다.']);
    exit;
}

// 필수 데이터 확인
if (!isset($_POST['type'], $_POST['title'], $_POST['content'])) {
    echo json_encode(['success' => false, 'message' => '오류: 필수 데이터(type, title, content)가 누락되었습니다.']);
    exit;
}

$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = $_POST['type'];
$title = $_POST['title'];
$content = $_POST['content'];

// 썸네일 경로 결정 로직
$thumbnail_path = $_POST['thumbnail'] ?? null;
if (empty($thumbnail_path)) {
    // 정규식을 사용하여 content에서 첫 번째 이미지 태그의 src 속성 값을 찾습니다.
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    // 일치하는 이미지가 있으면 해당 경로를, 없으면 null을 사용합니다.
    $thumbnail_path = $matches[1] ?? null;
}

// 데이터베이스 작업
try {
    if ($post_id > 0) { // 글 수정
        $stmt = $mysqli->prepare("UPDATE blz_posts SET title = ?, content = ?, thumbnail_path = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $thumbnail_path, $post_id);
    } else { // 새 글 작성
        $stmt = $mysqli->prepare("INSERT INTO blz_posts (type, title, content, thumbnail_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $type, $title, $content, $thumbnail_path);
    }

    if ($stmt->execute()) {
        $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
        echo json_encode(['success' => true, 'redirect_id' => $new_id]);
    } else {
        // SQL 실행 실패 시 오류 메시지를 포함하여 응답
        echo json_encode(['success' => false, 'message' => 'SQL 실행 실패: ' . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    // 그 외 예외 발생 시 오류 메시지 응답
    echo json_encode(['success' => false, 'message' => '서버 오류: ' . $e->getMessage()]);
}
?>