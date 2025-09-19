<?php
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('권한이 없습니다.'); history.back();</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_type = $_POST['board_type'] ?? '';
    $post_id = (int)($_POST['post_id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $is_secret = isset($_POST['is_secret']) ? 1 : 0;
    $table_name = 'posts_' . $board_type;
    $thumbnail_path = null;

    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $original_name = basename($_FILES["thumbnail"]["name"]);
        $safe_name = preg_replace("/[^A-Za-z0-9\._-]/", '', $original_name);
        $filename = 'thumb_' . time() . '_' . $safe_name;
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
            $thumbnail_path = 'uploads/' . $filename;
        }
    }

    if ($post_id > 0) {
        if ($thumbnail_path) {
            $sql = "UPDATE {$table_name} SET title = ?, content = ?, thumbnail = ?, is_secret = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssii', $title, $content, $thumbnail_path, $is_secret, $post_id);
        } else {
            $sql = "UPDATE {$table_name} SET title = ?, content = ?, is_secret = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssii', $title, $content, $is_secret, $post_id);
        }
    } else {
        $sql = "INSERT INTO {$table_name} (title, content, thumbnail, is_secret) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $title, $content, $thumbnail_path, $is_secret);
    }

    if ($stmt->execute()) {
        $new_post_id = $post_id > 0 ? $post_id : $conn->insert_id;
        echo "<script>
                alert('저장되었습니다.');
                window.location.hash = '/list_page_{$board_type}.php?id={$new_post_id}';
              </script>";
    } else {
        echo "<script>alert('데이터베이스 저장 중 오류가 발생했습니다.'); history.back();</script>";
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>