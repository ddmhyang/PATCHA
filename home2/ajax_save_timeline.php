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
$display_type = $_POST['display_type'] ?? 'dot';
$interval_height = isset($_POST['interval_height']) ? intval($_POST['interval_height']) : 150;
$thumbnail_path = null;


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


$mysqli->begin_transaction();
try {
    if ($post_id > 0) { 
        if (!$thumbnail_path) {
            $stmt_thumb = $mysqli->prepare("SELECT thumbnail FROM home2_timeline WHERE id = ?");
            $stmt_thumb->bind_param("i", $post_id);
            $stmt_thumb->execute();
            $thumbnail_path = $stmt_thumb->get_result()->fetch_assoc()['thumbnail'];
            $stmt_thumb->close();
        }
        
        $stmt = $mysqli->prepare("UPDATE home2_timeline SET type = ?, chapter = ?, title = ?, content = ?, thumbnail = ?, display_type = ?, interval_height = ? WHERE id = ?");
        $stmt->bind_param("ssssssii", $type, $chapter, $title, $content, $thumbnail_path, $display_type, $interval_height, $post_id);
        if (!$stmt->execute()) throw new Exception($stmt->error);

    } else { 
        
        $stmt = $mysqli->prepare("INSERT INTO home2_timeline (type, chapter, title, content, thumbnail, display_type, interval_height) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $type, $chapter, $title, $content, $thumbnail_path, $display_type, $interval_height);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        
        
        $post_id = $mysqli->insert_id;

        $views = ['overall', 'novel', 'roleplay', 'trpg'];
        $pos_stmt = $mysqli->prepare("INSERT INTO home2_timeline_positions (timeline_item_id, timeline_view, position_y, side) VALUES (?, ?, ?, ?)");

        foreach ($views as $view) {
            $max_y_query = "SELECT MAX(position_y) as max_y FROM home2_timeline_positions WHERE timeline_view = '" . $mysqli->real_escape_string($view) . "'";
            $max_y_result = $mysqli->query($max_y_query)->fetch_assoc();
            $initial_y = ($max_y_result && $max_y_result['max_y'] !== null) ? intval($max_y_result['max_y']) + 180 : 0;
            
            $pos_stmt->bind_param("isis", $post_id, $view, $initial_y, $side);
            if (!$pos_stmt->execute()) throw new Exception($pos_stmt->error);
        }
        $pos_stmt->close();
    }
    
    $mysqli->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장 실패: ' . $e->getMessage()]);
}

if(isset($stmt)) $stmt->close();
?>