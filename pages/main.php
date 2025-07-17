<?php
if (isset($_GET['spa_content']) && $_GET['spa_content'] === 'true') {
    require_once '../includes/db.php';
    $is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    $csrf_token = $_SESSION['csrf_token'] ?? '';
    $allowed_pages = [
        'page_view', 'gallery1', 'gallery2', 'gallery_etc', 'gallery_view', 'gallery_upload',
        'gallery_edit', 'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit', 'search',
        'timeline', 'timeline_edit', 'timeline_save', 'timeline_upload', 'timeline_view'
    ];
    $page = $_GET['page'] ?? 'page_view';
    if ($page === 'page_view' && !isset($_GET['name'])) {
        $_GET['name'] = 'eden';
    }
    if (in_array($page, $allowed_pages)) {
        ob_start();
        $page_file = $page . '.php';
        if (file_exists($page_file)) {
            include $page_file;
        } else {
            echo '<h1>페이지 파일을 찾을 수 없습니다.</h1>';
        }
        echo ob_get_clean();
    } else {
        http_response_code(404);
        echo '<h1>페이지를 찾을 수 없습니다.</h1>';
    }
    exit;
}

require_once '../includes/db.php';
if (!isset($_SESSION['player_logged_in']) || $_SESSION['player_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eden</title>
        <link rel="icon" type="image/png" href="../assets/img/기타/동물1.png">

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <link
            href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css"
            rel="stylesheet">
        <script
            src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

        <style>
            @font-face {
                font-family: 'Bonheur-Royale';
                src: url("../assets/fonts/Bonheur-Royale.ttf") format('truetype');
            }
            @font-face {
                font-family: 'Fre1';
                src: url("../assets/fonts/Freesentation-1Thin.ttf") format('truetype');
            }
            @font-face {
                font-family: 'Fre3';
                src: url("../assets/fonts/Freesentation-3Light.ttf") format('truetype');
            }
            @font-face {
                font-family: 'Fre7';
                src: url("../assets/fonts/Freesentation-7Bold.ttf") format('truetype');
            }
            @font-face {
                font-family: 'Fre9';
                src: url("../assets/fonts/Freesentation-9Black.ttf") format('truetype');
            }
            body,
            html {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                background-color: rgb(0, 0, 0);
                overflow: hidden;
                position: relative;
                visibility: hidden;
                font-family: 'Fre1', sans-serif;
            }
            .container {
                width: 1440px;
                height: 900px;
                background-color: #000000;
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
                flex-shrink: 0;
                aspect-ratio: 1440/900;
                background: url("../assets/img/background.png") rgb(0, 0, 0) 50% / cover no-repeat;
                overflow: hidden;
                border-left: 2px solid rgb(160, 160, 160);
                border-right: 2px solid rgb(160, 160, 160);
                box-sizing: border-box;
            }
            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }
            header {
                position: absolute;
                top: 58px;
                height: 56px;
                flex-shrink: 0;
                width: 100%;
            }
            .title {
                color: #FFF;
                text-align: center;
                font-family: "Bonheur-Royale";
                font-size: 48px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                position: absolute;
                left: 68px;
                text-decoration-line: none;
            }
            nav {
                position: absolute;
                top: 7px;
                left: 665px;
                display: flex;
                gap: 62px;
                cursor: pointer;
                align-items: center;
            }
            nav > a {
                width: 90px;
                color: #FFF;
                text-align: center;
                font-family: 'Fre1';
                font-size: 28px;
                font-style: normal;
                font-weight: 300;
                line-height: normal;
                text-decoration-line: none;
            }
            nav > a.active {
                font-family: 'Fre9';
            }

            .nav_login {
                width: 35px;
                height: 35px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                background: url("../assets/img/user2.png") center center / cover no-repeat;
            }
            .nav_menu {
                margin-left: -70px;
                width: 35px;
                height: 35px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                background: url("../assets/img/menu.png") center center / cover no-repeat;
            }
            .sub_menu {
                width: 486px;
                height: 900px;
                flex-shrink: 0;
                background: #000;
                position: absolute;
                right: -486px;
                transition: right 0.3s ease-in-out;
                z-index: 10000;
            }
            .sub_menu.show {
                right: 0;
            }
            .SM_menu {
                position: absolute;
                left: 27px;
                top: 30px;
                background: url("../assets/img/menu.png") center center / cover no-repeat;
                width: 40px;
                height: 40px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                cursor: pointer;
            }
            .SM_search {
                position: absolute;
                left: 413px;
                top: 30px;
                width: 40px;
                height: 40px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                background: url("../assets/img/search.png") center center / cover no-repeat;
                cursor: pointer;
            }
            .SM_title {
                position: absolute;
                top: 106px;
                left: 50%;
                transform: translateX(-50%);
                color: #FFF;
                text-align: center;
                font-family: "Bonheur-Royale";
                font-size: 48px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }
            .SM_pre_btn {
                position: absolute;
                top: 529px;
                left: 146px;
                width: 45px;
                height: 30px;
                flex-shrink: 0;
                aspect-ratio: 3/2;
                cursor: pointer;
            }
            .SM_play_btn {
                position: absolute;
                top: 529px;
                left: 50%;
                transform: translateX(-50%);
                width: 30px;
                height: 30px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                fill: #FFF;
                cursor: pointer;
            }
            .SM_next_btn {
                position: absolute;
                top: 529px;
                left: 295px;
                width: 45px;
                height: 30px;
                flex-shrink: 0;
                aspect-ratio: 3/2;
                cursor: pointer;
            }
            .SM_line {
                position: absolute;
                top: 599px;
                left: 50%;
                transform: translateX(-50%);
                width: 297px;
                height: 2px;
                background: #FFF;
            }
            .SM_music_name {
                position: absolute;
                top: 649px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                flex-direction: column;
                gap: 50px;
                cursor: pointer;
            }
            .SM_music_name > a {
                color: #FFF;
                text-align: center;
                font-family: "Bonheur-Royale";
                font-size: 28px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }
            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }
            .SM_music_img {
                position: absolute;
                top: 192px;
                left: 94.5px;
                width: 297px;
                height: 297px;
                flex-shrink: 0;
                border-radius: 297px;
                background: url("../assets/img/ld/ld투샷5.png") -37.615px 0 / 140% 100% no-repeat;
                animation: spin 10s linear infinite;
                animation-play-state: paused;
            }
            .SM_music_img.playing {
                animation-play-state: running;
            }
            .content {
                color: white;
            }
            .note-modal-backdrop {
                z-index: -50;
            }

            .note-modal-content {
                position: relative;
                top: 150px;
                background-color: rgba(0, 0, 0, 0.8);
                color: #ffffff;
                font-family: 'Fre7';
            }
            .note-modal-title {
                color: #ffffff;
            }
            .note-modal .note-form-label {
                color: #ffffff;
                font-family: 'Fre7', sans-serif !important;
                font-synthesis: none;
            }
            .note-form-control,
            .note-input {
                background-color: #555555;
                color: #ffffff;
            }
            .note-form-control-file::-webkit-file-upload-button {
                background: #777;
                color: white;
            }
            .note-btn-primary {
                background-color: #777;
            }
            .note-btn-primary:hover {
                background-color: #888;
            }
            .close {
                color: #ffffff !important;
                opacity: 0.7;
            }
            .note-editor.note-frame {
                background-color: white;
                color: black;
            }



            @media (max-width: 768px) {
                .container {
                    width: 720px;
                    height: 1280px;
                    flex-shrink: 0;
                    background: url("../assets/img/background.png") lightgray -664px 0px / 284.444% 100% no-repeat;
                    background-color: #000000;
                    transform-origin: top left;
                    position: absolute;
                    transform: scale(0);
                    aspect-ratio: 720/1280;
                    overflow: hidden;
                    border-left: 2px solid rgb(160, 160, 160);
                    border-right: 2px solid rgb(160, 160, 160);
                    box-sizing: border-box;
                }
                header {
                    position: absolute;
                    top: 58px;
                    height: 56px;
                    flex-shrink: 0;
                    width: 100%;
                }
                .title {
                    color: #FFF;
                    text-align: center;
                    font-family: "Bonheur-Royale";
                    font-size: 48px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: normal;
                    position: absolute;
                    left: 50%;
                    transform: translateX(-50%);
                    text-decoration-line: none;
                }
                nav {
                    position: absolute;
                    top: 93px;
                    left: 80px;
                    display: flex;
                    cursor: pointer;
                    align-items: center;
                }
                nav > a {
                    color: #FFF;
                    text-align: center;
                    font-family: 'Fre1';
                    font-size: 28px;
                    font-style: normal;
                    font-weight: 300;
                    line-height: normal;
                    text-decoration-line: none;
                }
                nav > a.active {
                    font-family: 'Fre9';
                }

                .nav_login {
                    position: absolute;
                    top: -81px;
                    left: 479px;
                    width: 35px;
                    height: 35px;
                    flex-shrink: 0;
                    aspect-ratio: 1/1;
                    background: url("../assets/img/user2.png") center center / cover no-repeat;
                }
                .nav_menu {
                    position: absolute;
                    top: -81px;
                    left: 639px;
                    margin-left: -70px;
                    width: 35px;
                    height: 35px;
                    flex-shrink: 0;
                    aspect-ratio: 1/1;
                    background: url("../assets/img/menu.png") center center / cover no-repeat;
                }
                .sub_menu {
                    width: 720px;
                    height: 1280px;
                    flex-shrink: 0;
                    background: #000;
                    position: absolute;
                    right: -720px;
                    transition: right 0.3s ease-in-out;
                    z-index: 10000;
                }
                .sub_menu.show {
                    right: 0;
                }
                .SM_menu {
                    position: absolute;
                    left: 43.86px;
                    top: 30px;
                    background: url("../assets/img/menu.png") center center / cover no-repeat;
                    width: 59.051px;
                    height: 59.051px;
                    flex-shrink: 0;
                    aspect-ratio: 59.05/59.05;
                    cursor: pointer;
                }
                .SM_search {
                    position: absolute;
                    left: 613.7px;
                    top: 30px;
                    width: 59.051px;
                    height: 59.051px;
                    flex-shrink: 0;
                    aspect-ratio: 59.05/59.05;
                    aspect-ratio: 1/1;
                    background: url("../assets/img/search.png") center center / cover no-repeat;
                    cursor: pointer;
                }
                .SM_title {
                    position: absolute;
                    top: 114px;
                    left: 50%;
                    transform: translateX(-50%);
                    color: #FFF;
                    text-align: center;
                    font-family: "Bonheur-Royale";
                    font-size: 48px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: normal;
                }
                .SM_pre_btn {
                    position: absolute;
                    top: 738.47px;
                    left: 194.77px;
                    width: 66.433px;
                    height: 44.288px;
                    flex-shrink: 0;
                    aspect-ratio: 66.43/44.29;
                    cursor: pointer;
                }
                .SM_play_btn {
                    position: absolute;
                    top: 738.47px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 44.288px;
                    height: 44.288px;
                    flex-shrink: 0;
                    aspect-ratio: 44.29/44.29;
                    fill: #FFF;
                    cursor: pointer;
                }
                .SM_next_btn {
                    position: absolute;
                    top: 738.47px;
                    left: 454.27px;
                    width: 66.433px;
                    height: 44.288px;
                    flex-shrink: 0;
                    aspect-ratio: 66.43/44.29;
                    cursor: pointer;
                }
                .SM_line {
                    position: absolute;
                    top: 841.81px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 438.456px;
                    background: #FFF;
                }
                .SM_music_name {
                    position: absolute;
                    top: 915.62px;
                    left: 50%;
                    transform: translateX(-50%);
                    display: flex;
                    flex-direction: column;
                    gap: 73.81px;
                    cursor: pointer;
                }
                .SM_music_name > a {
                    color: #FFF;
                    text-align: center;
                    font-family: "Bonheur-Royale";
                    font-size: 28px;
                    font-style: normal;
                    font-weight: 400;
                    line-height: normal;
                }
                @keyframes spin {
                    from {
                        transform: rotate(0deg);
                    }
                    to {
                        transform: rotate(360deg);
                    }
                }
                .SM_music_img {
                    position: absolute;
                    top: 240.96px;
                    left: 144.25px;
                    width: 438.456px;
                    height: 438.456px;
                    flex-shrink: 0;
                    border-radius: 438.456px;
                    background: url("../assets/img/ld/ld투샷5.png") -37.615px 0 / 140% 100% no-repeat;
                    animation: spin 10s linear infinite;
                    animation-play-state: paused;
                }
                .SM_music_img.playing {
                    animation-play-state: running;
                }
                .content {
                    color: white;
                }
                .note-modal-backdrop {
                    z-index: -50;
                }

                .note-modal-content {
                    position: relative;
                    top: 150px;
                    background-color: rgba(0, 0, 0, 0.8);
                    color: #ffffff;
                    font-family: 'Fre7';
                }
                .note-modal-title {
                    color: #ffffff;
                }
                .note-modal .note-form-label {
                    color: #ffffff;
                    font-family: 'Fre7', sans-serif !important;
                    font-synthesis: none;
                }
                .note-form-control,
                .note-input {
                    background-color: #555555;
                    color: #ffffff;
                }
                .note-form-control-file::-webkit-file-upload-button {
                    background: #777;
                    color: white;
                }
                .note-btn-primary {
                    background-color: #777;
                }
                .note-btn-primary:hover {
                    background-color: #888;
                }
                .close {
                    color: #ffffff !important;
                    opacity: 0.7;
                }
                .note-editor.note-frame {
                    background-color: white;
                    color: black;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <a class="title" href="../index.php">EDEN</a>
                <nav>
                    <a href="#/page_view?name=eden" data-page="page_view">Profile</a>
                    <a href="#/timeline" data-page="timeline">Story</a>
                    <a href="#/trpg" data-page="trpg">TRPG</a>
                    <a href="#/gallery1" data-page="gallery1">Gallery</a>

                    <?php if ($is_admin): ?>
                    <a href="logout.php" title="로그아웃">
                        <div class="nav_login"></div>
                    </a>
                <?php else: ?>
                    <a href="login.php" title="관리자 로그인">
                        <div class="nav_login"></div>
                    </a>
                    <?php endif; ?>

                    <div class="nav_menu"></div>
                </nav>
            </header>

            <div class="sub_menu">
                <div class="SM_menu"></div>
                <div class="SM_search"></div>
                <div class="SM_music">
                    <audio id="music-player"></audio>
                    <div class="SM_title">Main1</div>
                    <div class="SM_music_img"></div>
                    <svg
                        class="SM_pre_btn"
                        xmlns="http://www.w3.org/2000/svg"
                        width="45"
                        height="30"
                        viewbox="0 0 45 30"
                        fill="none"><path d="M15 15L37.5 27.9904V2.00962L15 15Z" fill="white"/><path d="M0 15L22.5 27.9904V2.00962L0 15Z" fill="white"/></svg>
                    <div class="SM_play_btn"></div>
                    <svg
                        class="SM_next_btn"
                        xmlns="http://www.w3.org/2000/svg"
                        width="45"
                        height="30"
                        viewbox="0 0 45 30"
                        fill="none"><path d="M30 15L7.5 27.9904V2.00962L30 15Z" fill="white"/><path d="M45 15L22.5 27.9904V2.00962L45 15Z" fill="white"/></svg>
                    <div class="SM_line"></div>
                    <div class="SM_music_name">
                        <a data-index="0">Main 1</a>
                        <a data-index="1">Main 2</a>
                        <a data-index="2">Main 3</a>
                    </div>
                </div>
            </div>

            <main class="content" id="content-container"></main>

        </div>

        <script>
            function adjustScale() {
                const container = document.querySelector('.container');
                if (!container) 
                    return;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                if (windowWidth <= 784) {
                    containerWidth = 720;
                    containerHeight = 1280;
                } else {
                    containerWidth = 1440;
                    containerHeight = 900;
                }

                const scale = Math.min(
                    windowWidth / containerWidth,
                    windowHeight / containerHeight
                );
                container.style.transform = `scale(${scale})`;
                container.style.left = `${ (windowWidth - containerWidth * scale) / 2}px`;
                container.style.top = `${ (windowHeight - containerHeight * scale) / 2}px`;
            }
            window.addEventListener('load', () => {
                adjustScale();
                document.body.style.visibility = 'visible';
            });
            window.addEventListener('resize', adjustScale);

            const navMenuBtn = document.querySelector('.nav_menu');
            const subMenu = document.querySelector('.sub_menu');
            const subMenuCloseBtn = document.querySelector('.SM_menu');

            navMenuBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                subMenu
                    .classList
                    .toggle('show');
            });

            subMenuCloseBtn.addEventListener('click', () => {
                subMenu
                    .classList
                    .remove('show');
            });

            document.addEventListener('click', (event) => {
                if (!subMenu.contains(event.target) && !navMenuBtn.contains(event.target)) {
                    subMenu
                        .classList
                        .remove('show');
                }
            });

            const musicPlayer = document.getElementById('music-player');
            const playBtn = document.querySelector('.SM_play_btn');
            const prevBtn = document.querySelector('.SM_pre_btn');
            const nextBtn = document.querySelector('.SM_next_btn');
            const musicImg = document.querySelector('.SM_music_img');
            const musicTitle = document.querySelector('.SM_title');
            const trackLinks = document.querySelectorAll('.SM_music_name > a');

            const playIconSVG = `<img src="../assets/img/play.svg" alt="재생" style="width: 100%; height: 100%; object-fit: contain;">`;
            const pauseIconImg = `<img src="../assets/img/stop.png" alt="정지" style="width: 100%; height: 100%; object-fit: contain;">`;

            const playlist = [
                {
                    title: 'Main 1',
                    src: '../assets/bgm/Main1.mp3',
                    backgroundStyle: "url('../assets/img/ld/ld투샷5.png') -37.615px 0px / 140% 100% no-repeat"
                }, {
                    title: 'Main 2',
                    src: '../assets/bgm/Main2.mp3',
                    backgroundStyle: "url('../assets/img/ld/ld11.jpg') center center / cover no-repeat"
                }, {
                    title: 'Main 3',
                    src: '../assets/bgm/Main3.mp3',
                    backgroundStyle: "url('../assets/img/ld/ld투샷2.jpg') center center / cover no-repeat"
                }
            ];
            let currentTrackIndex = 0;
            let isPlaying = false;

            function loadTrack(index) {
                const track = playlist[index];
                musicPlayer.src = track.src;
                musicTitle.textContent = track.title;
                musicImg.style.background = track.backgroundStyle;
                currentTrackIndex = index;
                musicImg
                    .classList
                    .remove('playing');
                void musicImg.offsetWidth;
                if (isPlaying) {
                    musicImg
                        .classList
                        .add('playing');
                }
            }

            function togglePlayPause() {
                if (isPlaying) {
                    musicPlayer.pause();
                    musicImg
                        .classList
                        .remove('playing');
                    playBtn.innerHTML = playIconSVG;
                    playBtn.style.paddingLeft = '10px';
                } else {
                    musicPlayer.play();
                    musicImg
                        .classList
                        .add('playing');
                    playBtn.innerHTML = pauseIconImg; 
                    playBtn.style.paddingLeft = '0px';
                }
                isPlaying = !isPlaying;
            }

            function playNext() {
                currentTrackIndex = (currentTrackIndex + 1) % playlist.length;
                loadTrack(currentTrackIndex);
                if (isPlaying) 
                    musicPlayer.play();
                }
            function playPrev() {
                currentTrackIndex = (currentTrackIndex - 1 + playlist.length) % playlist.length;
                loadTrack(currentTrackIndex);
                if (isPlaying) 
                    musicPlayer.play();
                }
            playBtn.addEventListener('click', (event) => {
                event.stopPropagation();
                togglePlayPause();
            });
            prevBtn.addEventListener('click', playPrev);
            nextBtn.addEventListener('click', playNext);
            musicPlayer.addEventListener('ended', playNext);
            trackLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const trackIndex = parseInt(link.dataset.index, 10);
                    loadTrack(trackIndex);
                    if (!isPlaying) {
                        togglePlayPause();
                    } else {
                        musicPlayer.play();
                    }
                });
            });

            window.addEventListener('load', () => {
                if (playlist.length > 0) {
                    loadTrack(0);
                    if (!isPlaying) {
                        togglePlayPause();
                    }
                }
            });

            $(document).ready(function () {
                const contentContainer = $('#content-container');
                const csrfToken = '<?php echo $csrf_token; ?>';

                function loadPage(url) {
                    const requestUrl = url + (
                        url.includes('?')
                            ? '&'
                            : '?'
                    ) + 'spa_content=true';
                    $.ajax({
                        url: requestUrl,
                        type: 'GET',
                        success: function (response) {
                            contentContainer.html(response);
                        },
                        error: function () {
                            contentContainer.html('<h1>페이지를 불러오는 데 실패했습니다.</h1>');
                        }
                    });
                }

                function router() {
                    document
                        .querySelector('.sub_menu')
                        .classList
                        .remove('show');
                    const path = window
                        .location
                        .hash
                        .substring(2) || 'page_view?name=eden';
                    const [page, queryString] = path.split('?');
                    const allowed_pages = [
                        'page_view',
                        'gallery1',
                        'gallery2',
                        'gallery_etc',
                        'gallery_view',
                        'gallery_upload',
                        'gallery_edit',
                        'trpg',
                        'trpg_view',
                        'trpg_upload',
                        'trpg_edit',
                        'search',
                        'timeline',
                        'timeline_view',
                        'timeline_upload',
                        'timeline_edit'
                    ];

                    if (allowed_pages.includes(page)) {
                        const contentUrl = `main.php?page=${page}${queryString
                            ? '&' + queryString
                            : ''}`;
                        loadPage(contentUrl);

                        $('nav > a').removeClass('active');
                        let currentPageGroup = page.startsWith('gallery')
                            ? 'gallery1'
                            : (
                                page.startsWith('trpg')
                                    ? 'trpg'
                                    : (
                                        page.startsWith('timeline')
                                            ? 'timeline'
                                            : page
                                    )
                            );
                        if (page.startsWith('page_view')) 
                            currentPageGroup = 'page_view';
                        $(`nav > a[data-page="${currentPageGroup}"]`).addClass('active');
                    } else {
                        window.location.hash = '#/page_view?name=eden';
                    }
                }

                $(document).on('click', 'a', function (e) {
                    const href = $(this).attr('href');
                    if (href && href.startsWith('#/')) {
                        e.preventDefault();
                        window.location.hash = href;
                    }
                });

                $(document).on('submit', 'form[action$="_save.php"]', function (e) {
                    e.preventDefault();
                    if ($(this).find('.note-editor').length) {
                        $('textarea[name="full_description"], textarea[name="content"]').each(
                            function () {
                                $(this).val($(this).summernote('code'));
                            }
                        );
                    }
                    var formData = new FormData(this);
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                alert(response.message || '성공적으로 처리되었습니다.');
                                if (response.redirect_url) {
                                    window.location.hash = response.redirect_url;
                                }
                            } else {
                                alert('오류: ' + response.message);
                            }
                        },
                        error: function () {
                            alert('요청 처리 중 오류가 발생했습니다.');
                        }
                    });
                });

                $(document).on('click', '.btn-delete', function (e) {
                    e.preventDefault();
                    if (!confirm('정말 삭제하시겠습니까?')) 
                        return;
                    
                    var postId = $(this).data('id');
                    var token = $(this).data('token');
                    var deleteUrl = $(this).data('url'); 
                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        data: {
                            id: postId,
                            token: token,
                            csrf_token: token
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response.success) {
                                alert('삭제되었습니다.');
                                if (response.redirect_url) {
                                    window.location.hash = response.redirect_url;
                                }
                            } else {
                                alert('삭제 실패: ' + response.message);
                            }
                        },
                        error: function () {
                            alert('요청 처리 중 오류가 발생했습니다.');
                        }
                    });
                });

                $('.SM_search')
                    .off('click')
                    .on('click', function () {
                        const query = prompt("검색어를 입력하세요:");
                        if (query) {
                            window.location.hash = `#/search?query=${encodeURIComponent(query)}`;
                        }
                    });

                $(window)
                    .on('hashchange', router)
                    .trigger('hashchange');
            });
        </script>
    </body>
</html>