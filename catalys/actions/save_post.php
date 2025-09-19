<?php
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('권한이 없습니다.'); history.back();</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board_type = $_POST['board_type'];
    $post_id = (int)$_POST['post_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $table_name = 'posts_' . $board_type;
    $thumbnail_path = '';

    // 썸네일 이미지 업로드 처리
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES["thumbnail"]["name"]);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
            $thumbnail_path = 'uploads/' . $filename;
        }
    }

    if ($post_id > 0) { // 수정
        if ($thumbnail_path) { // 새 썸네일이 업로드된 경우
            $sql = "UPDATE {$table_name} SET title = ?, content = ?, thumbnail = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $title, $content, $thumbnail_path, $post_id);
        } else { // 기존 썸네일 유지
            $sql = "UPDATE {$table_name} SET title = ?, content = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $title, $content, $post_id);
        }
    } else { // 새로 작성
        $sql = "INSERT INTO {$table_name} (title, content, thumbnail) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $title, $content, $thumbnail_path);
    }

    if ($stmt->execute()) {
        $new_post_id = $post_id > 0 ? $post_id : $conn->insert_id;
        echo "<script>alert('저장되었습니다.'); location.href='../list_page_{$board_type}.php?id={$new_post_id}';</script>";
    } else {
        echo "<script>alert('저장 중 오류가 발생했습니다.'); history.back();</script>";
    }
    $stmt->close();
    $conn->close();
}
?>