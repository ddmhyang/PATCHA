<?php
require_once 'db.php';

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

    if ($post_id > 0) { // 수정
        $sql = "UPDATE {$table_name} SET title = ?, content = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $title, $content, $post_id);
    } else { // 새로 작성
        $sql = "INSERT INTO {$table_name} (title, content) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $title, $content);
    }

    if ($stmt->execute()) {
        $new_post_id = $post_id > 0 ? $post_id : $conn->insert_id;
        echo "<script>alert('저장되었습니다.'); location.href='../list_page_{$board_type}.php?id={$new_post_id}';</script>";
    } else {
        echo "<script>alert('저장 중 오류가 발생했습니다.'); history.back();</script>";
    }
}
?>