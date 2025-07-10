<?php
// /pages/main.php - 통합 SPA 버전 (수정본)

// 1. AJAX 요청인지, 일반 페이지 로드인지 확인합니다.
//    URL에 'spa_content=true' 파라미터가 있으면 AJAX 요청으로 간주합니다.
if (isset($_GET['spa_content']) && $_GET['spa_content'] === 'true') {
    require_once '../includes/db.php';

    // AJAX 요청일 경우, 페이지의 '내용'만 HTML로 생성하여 반환합니다.
    $is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    $csrf_token = $_SESSION['csrf_token'] ?? '';

    $allowed_pages = [
        'page_view', 'gallery1', 'gallery2', 'gallery_etc', 'gallery_view', 'gallery_upload',
        'gallery_edit', 'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit', 'search', 'timeline'
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
    // AJAX 요청 처리는 여기서 끝납니다.
    exit;
}

// 2. 일반적인 페이지 로드일 경우, 전체 HTML 뼈대를 렌더링합니다.
//    이 코드는 사용자가 처음 사이트에 접속했을 때 한 번만 실행됩니다.
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

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
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
                left: 136px;
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
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <a class="title" href="#/page_view?name=eden">EDEN</a>
                <nav>
                    <a href="#/page_view?name=eden" data-page="page_view">Profile</a>
                    <a href="#/timeline" data-page="timeline">Story</a>
                    <a href="#/trpg" data-page="trpg">TRPG</a>
                    <a href="#/gallery1" data-page="gallery1">Gallery</a>

                    <?php if ($is_admin): ?>
                    <a href="logout.php" title="로그아웃"><div class="nav_login"></div></a>
                    <?php else: ?>
                    <a href="login.php" title="관리자 로그인"><div class="nav_login"></div></a>
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
                    <svg
                        class="SM_play_btn"
                        xmlns="http://www.w3.org/2000/svg"
                        width="23"
                        height="26"
                        viewbox="0 0 23 26"
                        fill="none"><path d="M23 13L0.5 25.9904V0.00961876L23 13Z" fill="white"/></svg>
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
                let containerWidth = 1440,
                    containerHeight = 900;
                const windowWidth = window.innerWidth,
                    windowHeight = window.innerHeight;
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
            navMenuBtn.addEventListener('click', () => {
                subMenu
                    .classList
                    .toggle('show');
            });
            subMenuCloseBtn.addEventListener('click', () => {
                subMenu
                    .classList
                    .remove('show');
            });

            const searchBtn = document.querySelector('.SM_search');
            searchBtn.addEventListener('click', () => {
                const query = prompt("검색어를 입력하세요:");
                if (query) {
                    window.location.href = `main.php?page=search&query=${encodeURIComponent(query)}`;
                }
            });

            const musicPlayer = document.getElementById('music-player');
            const playBtn = document.querySelector('.SM_play_btn');
            const prevBtn = document.querySelector('.SM_pre_btn');
            const nextBtn = document.querySelector('.SM_next_btn');
            const musicImg = document.querySelector('.SM_music_img');
            const musicTitle = document.querySelector('.SM_title');
            const trackLinks = document.querySelectorAll('.SM_music_name > a');
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
                } else {
                    musicPlayer.play();
                    musicImg
                        .classList
                        .add('playing');
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
            playBtn.addEventListener('click', togglePlayPause);
            prevBtn.addEventListener('click', playPrev);
            nextBtn.addEventListener('click', playNext);
            musicPlayer.addEventListener('ended', playNext);
            trackLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const trackIndex = parseInt(link.dataset.index, 10);
                    loadTrack(trackIndex);
                    if (!isPlaying) {
                        musicImg
                            .classList
                            .add('playing');
                        isPlaying = true;
                    }
                    musicPlayer.play();
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
            
            $(document).ready(function() {
                $(document).on('submit', 'form[action$="_save.php"]', function(e) {
                e.preventDefault(); 

                // Summernote 사용 시, 내용을 실제 textarea에 반영
                if ($(this).find('#summernote').length) {
                    $('#summernote').val($('#summernote').summernote('code'));
                }

                var formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('성공적으로 처리되었습니다.');
                            window.location.hash = response.redirect_url;
                        } else {
                            alert('오류: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('요청 처리 중 오류가 발생했습니다.');
                    }
                });
            });

            // [수정] 삭제 버튼 이벤트 핸들러
            $(document).on('click', '.btn-delete', function(e) {
                e.preventDefault();
                
                if (!confirm('정말 이 게시물을 삭제하시겠습니까?')) {
                    return;
                }

                var postId = $(this).data('id');
                var token = $(this).data('token');

                $.ajax({
                    url: 'gallery_delete.php', // 삭제 처리는 이 파일로 통일
                    type: 'POST',
                    data: {
                        id: postId,
                        token: token
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('삭제되었습니다.');
                            window.location.hash = response.redirect_url;
                        } else {
                            alert('삭제 실패: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('요청 처리 중 오류가 발생했습니다.');
                    }
                });
            });
                const contentContainer = $('#content-container');

                function loadPage(url) {
                    // AJAX 요청 URL에 'spa_content=true'를 추가하여 서버가 콘텐츠만 반환하도록 합니다.
                    const requestUrl = url + (url.includes('?') ? '&' : '?') + 'spa_content=true';

                    $.ajax({
                        url: requestUrl,
                        type: 'GET',
                        success: function(response) {
                            contentContainer.html(response);
                            // 동적으로 로드된 콘텐츠 안의 스크립트가 있다면 실행합니다.
                            contentContainer.find('script').each(function() {
                                if ($(this).text()) {
                                    eval($(this).text());
                                }
                            });
                        },
                        error: function() {
                            contentContainer.html('<h1>페이지를 불러오는 데 실패했습니다.</h1>');
                        }
                    });
                }

                function router() {
                    // 주소창의 #/ 뒤 경로를 가져옵니다. 없으면 기본 경로로 설정합니다.
                    const path = window.location.hash.substring(2) || 'page_view?name=eden';
                    const [page, queryString] = path.split('?');

                    // 콘텐츠를 요청할 URL을 만듭니다. (이 파일 자신에게 요청)
                    const contentUrl = `main.php?page=${page}${queryString ? '&' + queryString : ''}`;
                    loadPage(contentUrl);

                    // 메뉴 활성화 처리
                    $('nav > a').removeClass('active');
                    let currentPageGroup = page.startsWith('gallery') ? 'gallery1' : (page.startsWith('page_view') ? 'page_view' : page);
                    $(`nav a[data-page="${currentPageGroup}"]`).addClass('active');
                }

                // 이벤트 위임: 문서 전체의 모든 <a> 태그 클릭을 감지합니다.
                $(document).on('click', 'a', function(e) {
                    const href = $(this).attr('href');
                    // '#/'로 시작하는 링크만 SPA로 처리하고 나머지는 기본 동작을 따릅니다.
                    if (href && href.startsWith('#/')) {
                        e.preventDefault(); // a 태그의 기본 동작(페이지 새로고침)을 막습니다.
                        history.pushState(null, '', href); // 브라우저 주소창의 URL을 변경합니다.
                        router(); // 변경된 URL에 맞게 페이지를 로드합니다.
                    }
                });

                // [수정됨] 검색 기능이 SPA로 동작하도록 수정
                $('.SM_search').on('click', () => {
                    const query = prompt("검색어를 입력하세요:");
                    if (query) {
                        // 페이지를 리로드하는 대신 해시를 변경하여 라우터를 트리거합니다.
                        window.location.hash = `#/search?query=${encodeURIComponent(query)}`;
                    }
                });

                // 브라우저의 뒤로가기/앞으로가기 버튼을 눌렀을 때도 router를 실행합니다.
                window.addEventListener('popstate', router);

                // 첫 페이지 진입 시 router를 실행하여 기본 페이지를 로드합니다.
                router();
            });
        </script>
    </body>
</html>