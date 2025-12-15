<?php
require_once "../includes/db.php";

$name = $_POST['name'];
$content = $_POST['content'];
$date = date("Y-m-d H:i:s");

// Prepared Statement 사용 (보안 강화)
$stmt = $mysqli->prepare("INSERT INTO esmeray_guestbook (name, content, date) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $content, $date);

if($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $mysqli->error;
}

$stmt->close();
?>