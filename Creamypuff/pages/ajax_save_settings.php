<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

$current_settings_result = $mysqli->query("SELECT * FROM settings");
$current_settings = [];
while ($row = $current_settings_result->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}
function update_setting($key, $value, $mysqli) {
    $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    $stmt->execute();
    $stmt->close();
}

$uploadDir = '../assets/img/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

if (isset($_FILES['main_background']) && $_FILES['main_background']['error'] === UPLOAD_ERR_OK) {
    $fileName = 'bg_main_' . uniqid() . '.png';
    if (move_uploaded_file($_FILES['main_background']['tmp_name'], $uploadDir . $fileName)) {
        update_setting('main_background', '../assets/img/' . $fileName, $mysqli);
    }
}
echo json_encode(['success' => true, 'message' => '설정이 저장되었습니다. 페이지를 새로고침합니다.', 'redirect_url' => 'reload']);
?>