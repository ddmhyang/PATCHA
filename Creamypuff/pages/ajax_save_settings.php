<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// --- 캐릭터 이름 변경 시 채팅 로그 업데이트 ---
// 먼저 현재 설정된 캐릭터 이름을 DB에서 가져옵니다.
$current_settings_result = $mysqli->query("SELECT * FROM settings");
$current_settings = [];
while ($row = $current_settings_result->fetch_assoc()) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}
$old_char1_name = $current_settings['character1_name'] ?? 'Hyun';
$old_char2_name = $current_settings['character2_name'] ?? 'Chan';

// 폼에서 새로 입력된 캐릭터 이름을 받습니다.
$new_char1_name = $_POST['character1_name'];
$new_char2_name = $_POST['character2_name'];

// 만약 기존 이름과 새 이름이 다르다면,
if ($old_char1_name !== $new_char1_name) {
    // 'chat' 테이블에서 기존 이름으로 된 모든 메시지의 character_name을 새 이름으로 변경(UPDATE)합니다.
    $stmt = $mysqli->prepare("UPDATE chat SET character_name = ? WHERE character_name = ?");
    $stmt->bind_param("ss", $new_char1_name, $old_char1_name);
    $stmt->execute();
    $stmt->close();
}
// (캐릭터 2도 동일하게 처리)

// --- 설정 값 업데이트 함수 ---
// ajax_save_page.php와 유사하게 INSERT ... ON DUPLICATE KEY UPDATE 구문을 사용합니다.
function update_setting($key, $value, $mysqli) {
    $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    $stmt->execute();
    $stmt->close();
}

// 새 캐릭터 이름을 DB에 저장합니다.
update_setting('character1_name', $new_char1_name, $mysqli);
update_setting('character2_name', $new_char2_name, $mysqli);

// --- 파일 업로드 처리 ---
$uploadDir = '../assets/img/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

// 각 이미지 파일이 업로드되었는지 확인하고, 오류 없이 잘 도착했다면
if (isset($_FILES['index_button_image']) && $_FILES['index_button_image']['error'] === UPLOAD_ERR_OK) {
    // uniqid() 함수로 중복되지 않는 고유한 파일 이름을 생성합니다.
    $fileName = 'btn_index_' . uniqid() . '.png';
    // 임시 폴더에 있는 파일을 실제 저장 경로로 이동시킵니다.
    if (move_uploaded_file($_FILES['index_button_image']['tmp_name'], $uploadDir . $fileName)) {
        // 성공하면, DB에 해당 설정의 값(setting_value)을 새 파일 경로로 업데이트합니다.
        update_setting('index_button_image', '../assets/img/' . $fileName, $mysqli);
    }
}
// (main_background, chat_background, character1_image 등 다른 파일들도 동일하게 처리)

// 모든 작업이 끝나면 성공 메시지와 함께 페이지를 새로고침하라는 'reload' 지시를 응답합니다.
echo json_encode(['success' => true, 'message' => '설정이 저장되었습니다. 페이지를 새로고침합니다.', 'redirect_url' => 'reload']);
?>