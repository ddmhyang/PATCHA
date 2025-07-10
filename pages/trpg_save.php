<?php
// /pages/trpg_save.php
require_once '../includes/db.php';


if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    die("권한이 없습니다.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF 토큰이 유효하지 않습니다. 폼을 다시 제출해주세요.');
    }


    $title = $_POST['title'];
    $content = $_POST['content'];
    $gallery_type = $_POST['gallery_type'];
    $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $thumbnail = $_POST['thumbnail'] ?? null; 

    
    if (empty($thumbnail)) {
        preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
        if (isset($matches[1])) {
            $thumbnail = $matches[1];
        }
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
        
        header("Location: main.php?page=trpg_view&id=" . $new_id);
        exit;
    } else {
        die("저장에 실패했습니다: " . $stmt->error);
    }
    $stmt->close();
}
$mysqli->close();
?>