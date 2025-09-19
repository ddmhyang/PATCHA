<?php
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('권한이 없습니다.'); history.back();</script>";
    exit;
}

$board_type = isset($_GET['board']) ? $_GET['board'] : '';
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($board_type && $post_id > 0) {
    $table_name = 'posts_' . $board_type;
    $sql = "DELETE FROM {$table_name} WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $post_id);

    if ($stmt->execute()) {
        echo "<script>alert('삭제되었습니다.'); location.href='../list_{$board_type}.php';</script>";
    } else {
        echo "<script>alert('삭제 중 오류가 발생했습니다.'); history.back();</script>";
    }
} else {
    echo "<script>alert('잘못된 접근입니다.'); history.back();</script>";
}
?>