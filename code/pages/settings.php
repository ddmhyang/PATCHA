<?php
require_once '../includes/db.php';
// 관리자가 아니면 'die()' 함수로 스크립트를 중단시키고 메시지를 출력합니다.
if (!$is_admin) { die("관리자만 접근 가능합니다."); }

// 'chan_settings' 테이블에서 모든 설정 값을 가져옵니다.
$settings_result = $mysqli->query("SELECT * FROM chan_settings");
$settings = [];
// while 반복문으로 가져온 설정들을 'setting_key' => 'setting_value' 형태로 $settings 배열에 저장합니다.
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="settings-container">
    <h2>사이트 설정</h2>
    <form class="ajax-form" action="ajax_save_settings.php" method="post" enctype="multipart/form-data">
        <hr>
        <div class="form-group">
            <label for="index_button_image">입장 버튼 이미지</label>
            <label for="index_button_image" class="file-upload-button">파일 선택</label>
            <input type="file" id="index_button_image" name="index_button_image" style="display: none;">
            <p>현재 이미지: <?php echo basename($settings['index_button_image']); ?></p>
        </div>
        <hr>
        <div class="form-group">
            </div>
        <hr>
        <div class="form-group">
            </div>
        <hr>
        <div class="form-group">
            <label for="character1_name">캐릭터 1 이름</label>
            <input type="text" id="character1_name" name="character1_name" value="<?php echo htmlspecialchars($settings['character1_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="character2_name">캐릭터 2 이름</label>
            <input type="text" id="character2_name" name="character2_name" value="<?php echo htmlspecialchars($settings['character2_name']); ?>" required>
        </div>
        <hr>
        <div class="form-group">
            <label for="character1_image">캐릭터 1 프로필 사진</label>
            <label for="character1_image" class="file-upload-button">파일 선택</label>
            <input type="file" id="character1_image" name="character1_image" style="display: none;">
            <p>현재 이미지: <img src="<?php echo htmlspecialchars($settings['character1_image']); ?>" height="50"></p>
        </div>
        <hr>
        <button class="submit_btn" type="submit">설정 저장</button>
    </form>
</div>