<?php
require_once 'includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = $_POST['type'];
$chapter = $_POST['chapter'];
$title = $_POST['title'];
$content = $_POST['content'];
$side = $_POST['side'] ?? 'left';
$thumbnail_path = null;

// 썸네일 처리 로직 (이전과 동일)
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    $newFileName = 'thumb-' . uniqid() . '.' . $ext;
    $targetFile = $uploadDir . $newFileName;
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
        $thumbnail_path = $targetFile;
    }
} else if (empty($thumbnail_path) && !empty($content)) {
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    if (isset($matches[1])) {
        $thumbnail_path = ltrim(parse_url($matches[1], PHP_URL_PATH), '/');
    }
}

// 트랜잭션 시작
$mysqli->begin_transaction();

try {
    if ($post_id > 0) { // 게시물 수정
        if (!$thumbnail_path) {
            $stmt_thumb = $mysqli->prepare("SELECT thumbnail FROM home2_timeline WHERE id = ?");
            $stmt_thumb->bind_param("i", $post_id);
            $stmt_thumb->execute();
            $thumbnail_path = $stmt_thumb->get_result()->fetch_assoc()['thumbnail'];
            $stmt_thumb->close();
        }
        $stmt = $mysqli->prepare("UPDATE home2_timeline SET type = ?, chapter = ?, title = ?, content = ?, thumbnail = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $type, $chapter, $title, $content, $thumbnail_path, $post_id);
        $stmt->execute();

    } else { // 새 게시물 작성
        $stmt = $mysqli->prepare("INSERT INTO home2_timeline (type, chapter, title, content, thumbnail) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $type, $chapter, $title, $content, $thumbnail_path);
        $stmt->execute();
        $post_id = $mysqli->insert_id; // 새로 생성된 게시물 ID

        // 4가지 뷰에 대한 기본 위치 정보 생성
        $views = ['overall', 'novel', 'roleplay', 'trpg'];
        $pos_stmt = $mysqli->prepare("INSERT INTO home2_timeline_positions (timeline_item_id, timeline_view, position_y, side) VALUES (?, ?, ?, ?)");
        $initial_y = 99999; // 맨 아래에 위치하도록 큰 값 부여
        foreach ($views as $view) {
            $pos_stmt->bind_param("isis", $post_id, $view, $initial_y, $side);
            $pos_stmt->execute();
        }
    }
    
    // 모든 쿼리가 성공하면 커밋
    $mysqli->commit();
    echo json_encode(['success' => true]);

} catch (mysqli_sql_exception $exception) {
    // 오류 발생 시 롤백
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장 실패: ' . $exception->getMessage()]);
}

$stmt->close();
?>