<?php
require_once './includes/db.php';
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>title</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/a11y-dark.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/go.min.js"></script>
        <style>
            @font-face {
                font-family: 'Fre1';
                src: url("assets/fonts/Freesentation-1Thin.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre3';
                src: url("assets/fonts/Freesentation-3Light.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre5';
                src: url("assets/fonts/Freesentation-5Medium.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            @font-face {
                font-family: 'Fre9';
                src: url("assets/fonts/Freesentation-9Black.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
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
                font-family: 'S-CoreDream-3Light', sans-serif;
            }

            .container {
                width: 1440px;
                height: 960px;
                flex-shrink: 0;
                aspect-ratio: 3/2;
                background: url("/assets/images/ny.png") no-repeat center center;
                background-size: cover;
                background-color: #000000;
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
            }

            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }

            @media (max-width: 768px) {
                .container {
                    width: 720px;
                    height: 1280px;
                    flex-shrink: 0;
                    background: url("/assets/images/ny.png") no-repeat center center;
                    background-size: cover;
                    background-color: #000000;
                    transform-origin: top left;
                    position: absolute;
                    transform: scale(0);
                }

                .container,
                body,
                html {
                    transition: background-color 1s ease-in-out;
                }
            }
        </style>
    </head>
    <body>
        <!-- <div class="container" onclick="location.href='pages/main.php#/'"> -->
        <div class="container">

            <audio id="music-player" loop>
                <source src="assets/bgm/music.mp3" type="audio/mpeg">
                오디오 오류. 문의주세요.
            </audio>
        </div>
        <script>
            $(document).ready(function() {
                //뮤직!!!
                const musicPlayer = document.getElementById('music-player');
                const playButton = $('.container');

                if (musicPlayer.paused) {
                    playButton.removeClass('playing');
                } else {
                    playButton.addClass('playing');
                }

                playButton.on('click', function() {
                    if (musicPlayer.paused) {
                        musicPlayer.play();
                        $(this).addClass('playing');
                    } else {
                        musicPlayer.pause();
                        $(this).removeClass('playing');
                    }
                });

                $(musicPlayer).on('ended', function() {
                    playButton.removeClass('playing');
                });
                
                $(document).one('click', function() {
                    if (musicPlayer.paused) {
                        musicPlayer.play().then(() => {
                            playButton.addClass('playing');
                        }).catch(error => {
                        });
                    }
                });
            });


            //크기조절!
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
        </script>
    </body>
</html>