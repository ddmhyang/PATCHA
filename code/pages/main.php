<?php 
// 데이터베이스 연결 및 세션 시작, 관리자 여부 확인을 위해 db.php 파일을 포함합니다.
require_once '../includes/db.php'; 

// 'chan_settings' 테이블에서 모든 설정 값을 가져옵니다.
$settings_result = $mysqli->query("SELECT * FROM chan_settings");
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
$main_bg = $settings['main_background'] ?? '../assets/images/default_main_bg.png';
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
        <div class="mobile_sub_menu_overlay"></div>
        <div class="logo"></div>
        <div class="mobile_border">
            <header>
                <nav>
                    <a href="#/main_content">Main</a>
                    <a href="#/chanlan">ChanLan</a>
                    <a href="#/chat">Chat</a>
                    <a href="#/gallery">Gallery</a>
                    <?php if ($is_admin):?>
                        <a href="logout.php">Logout</a> <?php else:?>
                        <a href="#/login">Login</a> <?php endif;?>
                </nav>
            </header>

            <div class="desktop_nav">
                <div class="chanlan_nav_container" style="display: none;">
                    </div>

                <div class="index_panel2">
                    <audio id="music-player" loop>
                        <source src="../assets/bgm/music.mp3" type="audio/mpeg">
                    </audio>
                </div>
                <div class="index_panel3">
                    <div class="dday">
                        <!-- 디데이 기능 -->
                        <?php
                            // 목표 날짜를 DateTime 객체로 생성합니다.
                            $target_date = new DateTime("2025-06-11");
                            // 현재 날짜를 DateTime 객체로 생성합니다.
                            $current_date = new DateTime();
                            // 두 날짜의 차이를 계산하여 DateInterval 객체를 반환받습니다.
                            $interval = $current_date->diff($target_date);
                            // 차이에서 전체 일수만 뽑아냅니다.
                            $d_day = $interval->days;
                            // 현재가 목표 날짜보다 미래라면 (날짜가 지났다면)
                            if ($current_date > $target_date) {
                                echo "D+" . ($d_day + 1); // D+Day 형식으로 출력
                            } else { // 아직 날짜가 남았다면
                                echo "D-" . $d_day; // D-Day 형식으로 출력
                            }
                        ?>
                    </div>
                </div>
            </div>

            <main id="content"></main>

            <div id="chat-overlay" style="display: none;"></div>
            
        </div>
    </div> 
    <script src="../assets/js/main.js"></script>
</body>
</html>