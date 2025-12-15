<?php
require_once "../includes/db.php";

// 관리자가 아니면 삭제 불가
if (!$is_admin) {
    die("Permission denied");
}

$id = $_POST['id'];

// Prepared Statement 사용
$stmt = $mysqli->prepare("DELETE FROM esmeray_guestbook WHERE id = ?");
$stmt->bind_param("i", $id);

if($stmt->execute()) {
    echo "success";
} else {
    echo "fail: " . $mysqli->error;
}

$stmt->close();
?>