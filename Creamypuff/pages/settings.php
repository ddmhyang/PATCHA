<?php
require_once '../includes/db.php';
if (!$is_admin) { die("관리자만 접근 가능합니다."); }

$settings_result = $mysqli->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="settings-container">
    <h2>사이트 설정</h2>
    <form class="ajax-form" action="ajax_save_settings.php" method="post" enctype="multipart/form-data">
        <hr>
        <div class="form-group">
            <label for="main_background">메인 배경화면</label>
            <label for="main_background" class="file-upload-button">파일 선택</label>
            <input type="file" id="main_background" name="main_background" style="display: none;">
            <p style="font-family: Fre1; font-size:16px">현재 이미지: <?php echo basename($settings['main_background']); ?></p>
        </div>
        <hr>
        <button class="submit_btn" type="submit">설정 저장</button>
    </form>
</div>