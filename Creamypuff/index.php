<?php
require_once 'includes/db.php';

$settings_result = $mysqli->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$main_bg = $settings['main_background'] ?? '/assets/images/background.png';
?><!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creamypuff</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body>
    <div class="container" style="background-image: url('<?php echo htmlspecialchars($main_bg); ?>');">
        <div class="index_layout" onclick="location.href='pages/main.php#/'">
            <div class="index_layout_top">
                <div class="iT_btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="153" height="31" viewBox="0 0 153 31" fill="none">
                    <line x1="1.5" y1="14.5" x2="26.5" y2="14.5" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <line x1="131.121" y1="5" x2="150.213" y2="24.0919" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <line x1="1.5" y1="-1.5" x2="28.5" y2="-1.5" transform="matrix(-0.707107 0.707107 0.707107 0.707107 152.558 5)" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <rect x="64.5" y="9.5" width="20" height="20" rx="1.5" stroke="white" stroke-width="3"/>
                    <rect x="72.5" y="1.5" width="20" height="21" rx="1.5" stroke="white" stroke-width="3"/>
                    </svg>
                </div>
            </div>
            <a>Creamypuff</a>
        </div>
    </div> 
    <script src="../assets/js/main.js"></script>
</body>
</html>