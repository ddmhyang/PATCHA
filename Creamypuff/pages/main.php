<?php 
// 데이터베이스 연결 및 세션 시작, 관리자 여부 확인을 위해 db.php 파일을 포함합니다.
require_once '../includes/db.php'; 

// 'settings' 테이블에서 모든 설정 값을 가져옵니다.
$settings_result = $mysqli->query("SELECT * FROM settings");
// 설정 값을 담을 빈 배열을 선언합니다.
$settings = [];
// while 반복문을 사용해 가져온 설정 값들을 한 줄씩 처리합니다.
while ($row = $settings_result->fetch_assoc()) {
    // 'setting_key'를 키로, 'setting_value'를 값으로 하여 $settings 배열에 저장합니다.
    // 예: $settings['main_background'] = '../assets/img/bg.png';
    $settings[$row['setting_key']] = $row['setting_value'];
}
// $settings 배열에서 'main_background' 값을 찾습니다.
// 만약 값이 없으면(?? 연산자), 오른쪽의 기본 이미지 경로를 $main_bg 변수에 할당합니다.
$main_bg = $settings['main_background'] ?? '/assets/images/background.png';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChanLan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body>
    <div class="container" style="background-image: url('<?php echo htmlspecialchars($main_bg); ?>');">

        <nav>
            <a href="#/main_content">Creamypuff</a>
            <a href="#/profile">Profile</a>
            <a href="#/gallery">Gallery</a>
            <a href="#/panel">Panel</a>
            <?php if ($is_admin === true): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="#/login">Login</a>
            <?php endif; ?>
            <a href="#/settings">Settings</a>
        </nav>

        <div class="pages_layout">
            <div class="pages_layout_top">
                <div class="pT_btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="153" height="31" viewBox="0 0 153 31" fill="none">
                    <line x1="1.5" y1="14.5" x2="26.5" y2="14.5" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <line x1="131.121" y1="5" x2="150.213" y2="24.0919" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <line x1="1.5" y1="-1.5" x2="28.5" y2="-1.5" transform="matrix(-0.707107 0.707107 0.707107 0.707107 152.558 5)" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <rect x="64.5" y="9.5" width="20" height="20" rx="1.5" stroke="white" stroke-width="3"/>
                    <rect x="72.5" y="1.5" width="20" height="21" rx="1.5" stroke="white" stroke-width="3"/>
                    </svg>
                </div>
            </div>

            <main id="content"></main>

        </div>
    </div> 
    <script src="../assets/js/main.js"></script>
</body>
</html>