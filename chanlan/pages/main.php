<?php require_once '../includes/db.php'; 
$settings_result = $mysqli->query("SELECT * FROM chan_settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$main_bg = $settings['main_background'] ?? '../assets/images/default_main_bg.png';
?>
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>ChanLan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
</head>
<body>
    <div class="container" style="background-image: url('<?php echo htmlspecialchars($main_bg); ?>');">

        <div class="logo"></div>
        <header>
            <nav>
                <a href="#/main_content">Main</a>
                <a href="#/chanlan">ChanLan</a>
                <!-- <a href="#/hyun">Hyun</a> -->
                <!-- <a href="#/chan">Chan</a> -->
                <a href="#/chat">Chat</a>
                <a href="#/gallery">Gallery</a>
                <!-- <a href="#/trpg">TRPG</a> -->
                <?php if ($is_admin): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="#/login">Login</a>
                <?php endif; ?>
            </nav>
        </header>

        <div class="chanlan_nav_container" style="display: none;">
            <div class="chanlan_nav1"></div>
            <div class="chanlan_nav2"></div>
            <div class="chanlan_nav3"></div>
        </div>

        <div class="gallery_nav_container" style="display: none;">
            <div class="gallery_nav"></div>
            <div class="trpg_nav"></div>
        </div>

        <div class="index_panel1"></div>
        <div class="index_panel2">
            <audio id="music-player" loop>
                <source src="../assets/bgm/music.mp4" type="audio/mpeg">
                오디오 오류. 문의주세요.
            </audio>
        </div>
        <div class="index_panel3">
            <div class="dday">
                <?php
                    $target_date = new DateTime("2025-06-11");
                    $current_date = new DateTime();

                    $interval = $current_date->diff($target_date);

                    $d_day = $interval->days;

                    if ($current_date > $target_date) {
                        echo "D+" . $d_day;
                    } else {
                        echo "D-" . $d_day;
                    }
                ?>
            </div>
        </div>

        <main id="content"></main>

        <div id="chat-overlay" style="display: none;">
        </div>

        <div class="index_ling">
            <svg xmlns="http://www.w3.org/2000/svg" width="118" height="30" viewBox="0 0 118 30" fill="none">
            <rect width="118" height="30" rx="15" fill="#7078A7"/>
            <rect x="5" y="5" width="108" height="20" rx="10" fill="#8A94CD"/>
            </svg>
            
            <svg xmlns="http://www.w3.org/2000/svg" width="118" height="30" viewBox="0 0 118 30" fill="none">
            <rect width="118" height="30" rx="15" fill="#7078A7"/>
            <rect x="5" y="5" width="108" height="20" rx="10" fill="#8A94CD"/>
            </svg>
            
            <svg xmlns="http://www.w3.org/2000/svg" width="118" height="30" viewBox="0 0 118 30" fill="none">
            <rect width="118" height="30" rx="15" fill="#7078A7"/>
            <rect x="5" y="5" width="108" height="20" rx="10" fill="#8A94CD"/>
            </svg>
            
            <svg xmlns="http://www.w3.org/2000/svg" width="118" height="30" viewBox="0 0 118 30" fill="none">
            <rect width="118" height="30" rx="15" fill="#7078A7"/>
            <rect x="5" y="5" width="108" height="20" rx="10" fill="#8A94CD"/>
            </svg>
        </div>

    </div> 
    <script src="../assets/js/main.js"></script>
</body>
</html>