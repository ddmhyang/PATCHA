<?php
require_once '../includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DdmHyang</title>
        <link rel="icon" type="image/png" sizes="128x128" href="../assets/images/favicon.png">
        <link
            href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Noto+Sans+KR:wght@300;400;500&display=swap"
            rel="stylesheet">
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <link rel="stylesheet" href="../assets/css/style.css"> 
    </head>
    <body>
        <div class="container">
            <main id="content"></main>

            <iframe id="song" width="0" height="0"
            src="https://www.youtube.com/embed/5LrLW-T7D-w?enablejsapi=1&autoplay=1&mute=0&controls=0&loop=1&playlist=5LrLW-T7D-w&si=jY1LUv2wtgFb"
            title="YouTube video player" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen></iframe>
            <button class="music-toggle-btn tBOff">
                <i class="fa-solid fa-heart"></i>
            </button>
            <button class="login-btn">
                <?php if ($is_admin): ?>
                    <a href="logout.php">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                <?php else: ?>
                    <a href="#/login">
                        <i class="fa-solid fa-heart"></i>
                    </a>
                <?php endif; ?>
            </button>
        </div>
        <script>
            $(document).ready(function() {
                const contentContainer = $('#content');

                function loadPage(url) {
                    $.ajax({
                        url: url, type: 'GET',
                        success: (response) => {
                            contentContainer.html(response);
                        },
                        error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
                    });
                }
                

                function router() {
                    const hash = window.location.hash.substring(2) || 'main_content';
                    const [page, params] = hash.split('?');
                    
                        const url = `${page}.php${params ? '?' + params : ''}`;
                        loadPage(url);
                }

                $(window).on('hashchange', router);
                router();


                window.uploadSummernoteImage = function(file, editor) {
                    let data = new FormData();
                    data.append("file", file);
                    $.ajax({
                        url: 'ajax_upload_image.php',
                        type: "POST", data: data,
                        contentType: false, processData: false, dataType: 'json',
                        success: function(response) {
                            if (response.success && response.url) {
                                $(editor).summernote('insertImage', response.url);
                            } else {
                                alert('이미지 업로드 실패: ' + (response.message || '알 수 없는 오류'));
                            }
                        },
                        error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
                    });
                };

                //전송
                $(document).on('submit', 'form.ajax-form', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const formData = new FormData(this);

                    if (form.find('.summernote').length) {
                        formData.set('content', form.find('.summernote').summernote('code'));
                    }

                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: formData, 
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: (response) => {
                            if (response.success) {
                                alert(response.message || '성공적으로 처리되었습니다.');
                                if (response.redirect_url === 'reload') {
                                    window.location.reload();
                                } else if (response.redirect_url) {
                                    window.location.hash = response.redirect_url;
                                } else {
                                    router();
                                }
                            } else {
                                alert('오류: ' + response.message);
                            }
                        },
                        error: () => alert('요청 처리 중 오류가 발생했습니다.')
                    });
                });
                
                //삭제
                $(document).on('click', '.delete-btn', function() {
                    if (!confirm('정말로 삭제하시겠습니까?')) return;

                    const id = $(this).data('id');
                    const type = $(this).data('type');

                    $.ajax({
                        url: 'ajax_delete_gallery.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if(response.success) {
                                alert('삭제되었습니다.');
                                window.location.hash = `#/${type}`;
                            } else {
                                alert('삭제 실패: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('삭제 요청 중 서버 오류가 발생했습니다.');
                        }
                    });
                });


            //크기조절
            function adjustScale() {
                const container = document.querySelector('.container');
                    if (!container) 
                        return;
                    const windowWidth = window.innerWidth,
                        windowHeight = window.innerHeight;
                    let containerWidth,
                        containerHeight;
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
            });

            var tag = document.createElement('script');
            tag.src = "https://www.youtube.com/iframe_api";
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

            var player;
            function onYouTubeIframeAPIReady() {
                player = new YT.Player('song', {
                    events: {
                        'onReady': onPlayerReady
                    }
                });
            }

            function onPlayerReady(event) {
                const musicBtn = document.querySelector('.tBOff');
                
                if(musicBtn) {
                    musicBtn.addEventListener('click', function() {
                        if (player && typeof player.getPlayerState === 'function') {
                            
                            var playerState = player.getPlayerState();

                            if (playerState == YT.PlayerState.PLAYING) {
                                player.pauseVideo();
                            } 
                            else {
                                player.playVideo();
                            }
                        }
                    });
                }
            }
        </script>
    </body>
</html>