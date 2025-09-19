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
    $thumbnail_path = null;

    // 썸네일 이미지 업로드 처리
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
            // DB에 저장할 경로는 웹 루트 기준
            $thumbnail_path = 'uploads/' . $filename;
        }
    }

    if ($post_id > 0) { // 게시글 수정
        if ($thumbnail_path) {
            // 새 썸네일이 업로드된 경우: 썸네일, 제목, 내용을 모두 업데이트
            $sql = "UPDATE {$table_name} SET title = ?, content = ?, thumbnail = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssi', $title, $content, $thumbnail_path, $post_id);
        } else {
            // 썸네일 업로드 없이 기존 글만 수정하는 경우
            $sql = "UPDATE {$table_name} SET title = ?, content = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $title, $content, $post_id);
        }
    } else { // 새 게시글 작성
        $sql = "INSERT INTO {$table_name} (title, content, thumbnail) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sss', $title, $content, $thumbnail_path);
    }

    if ($stmt->execute()) {
        $new_post_id = $post_id > 0 ? $post_id : $conn->insert_id;
        echo "<script>alert('저장되었습니다.'); location.href='../list_page_{$board_type}.php?id={$new_post_id}';</script>";
    } else {
        echo "<script>alert('데이터베이스 저장 중 오류가 발생했습니다.'); history.back();</script>";
    }
    $stmt->close();
    $conn->close();
}
?>