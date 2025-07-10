<?php
// /pages/main.php
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
        /* 기존 CSS는 그대로 유지합니다. */
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
            z-index: 100;
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
        /* ... (나머지 CSS는 기존과 동일하게 유지) ... */
        .content {
            color: white;
            /* 내용이 동적으로 로드될 컨테이너 */
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
                    <a href="logout.php" title="로그아웃" style="width:auto;"><div class="nav_login"></div></a>
                <?php else: ?>
                    <a href="login.php" title="관리자 로그인" style="width:auto;"><div class="nav_login"></div></a>
                <?php endif; ?>
                <div class="nav_menu" style="cursor:pointer;"></div>
            </nav>
        </header>

        <div class="sub_menu">
             </div>

        <div class="content">
            </div>
    </div>

    <script>
        // 기존의 adjustScale, 사이드 메뉴, 뮤직 플레이어 스크립트는 그대로 유지합니다.
        function adjustScale() {
            const container = document.querySelector('.container');
            if (!container) return;
            let containerWidth = 1440, containerHeight = 900;
            const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
            const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
            container.style.transform = `scale(${scale})`;
            container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
            container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
        }
        window.addEventListener('load', () => {
            adjustScale();
            document.body.style.visibility = 'visible';
        });
        window.addEventListener('resize', adjustScale);

        // ... (기존 뮤직 플레이어, 사이드 메뉴 관련 JS 코드) ...

        // =============================================================
        // =============================================================
        // SPA 라우팅 로직 (안정화 버전)
        // =============================================================
        $(document).ready(function() {
            const contentContainer = $('.content');

            function loadPage(url) {
                // 로딩 중 표시 (선택 사항)
                contentContainer.html('<h2>Loading...</h2>');
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        contentContainer.html(response);
                        // 동적으로 로드된 컨텐츠 내부의 스크립트를 찾아 실행
                        contentContainer.find('script').each(function() {
                            try {
                                if ($(this).text()) {
                                    eval($(this).text());
                                }
                            } catch (e) {
                                console.error("Error executing script:", e);
                            }
                        });
                    },
                    error: function() {
                        contentContainer.html('<h1>페이지를 불러오는 데 실패했습니다.</h1>');
                    }
                });
            }

            function router() {
                const path = window.location.hash.substring(2) || 'page_view?name=eden';
                const [page, queryString] = path.split('?');
                
                const apiUrl = `api.php?page=${page}${queryString ? '&' + queryString : ''}`;
                loadPage(apiUrl);

                // Active 클래스 관리
                $('nav > a').removeClass('active');
                let currentPageGroup = page.startsWith('gallery') ? 'gallery1' : (page.startsWith('page_view') ? 'page_view' : page);
                $(`nav a[data-page="${currentPageGroup}"]`).addClass('active');
            }

            // 이벤트 위임을 사용하여 문서 전체의 a 태그 클릭을 처리
            $(document).on('click', 'a', function(e) {
                const href = $(this).attr('href');

                // href가 없거나, 외부 링크이거나, 단순 앵커 링크, javascript 링크는 무시
                if (!href || href.startsWith('http') || href.startsWith('javascript') || href.trim() === '#') {
                    return;
                }

                // SPA 내부 링크(#/로 시작)인 경우에만 라우팅 처리
                if (href.startsWith('#/')) {
                    e.preventDefault();
                    history.pushState(null, '', href);
                    router();
                }
            });

            // 뒤로가기/앞으로가기 처리
            window.addEventListener('popstate', router);
            
            // 초기 페이지 로드
            router();
        });
    </script>
</body>
</html>