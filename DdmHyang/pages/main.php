<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DdmHyang</title>
        <link
            href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Noto+Sans+KR:wght@300;400;500&display=swap"
            rel="stylesheet">
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../assets/css/style.css"> 
    </head>
    <body>
        <div class="container">
            <div class="main-frame">
                <div class="deco-tape tape-1">Hello</div>
                <div class="deco-tape tape-2">World !</div>

                <div class="left-section">
                    <i class="fa-solid fa-butterfly floating-icon fi-1"></i>
                    <i class="fa-solid fa-star floating-icon fi-2"></i>
                    <i class="fa-solid fa-music floating-icon fi-3"></i>

                    <div class="sub-title">
                        [Warning]</div>
                    <h1> Love Error!</h1>
                    <p class="description">
                        미행 (그림자: Shadow) · f(x)
                    </p>
                </div>

                <div class="right-section">
                    <a href="intro.php" class="menu-btn">
                        INTRO
                        <i class="fa-solid fa-heart"></i>
                    </a>
                    <a href="novel.php" class="menu-btn">
                        NOVEL
                        <i class="fa-solid fa-book"></i>
                    </a>
                    <a href="art.php" class="menu-btn">
                        ARTWORKS
                        <i class="fa-solid fa-palette"></i>
                    </a>
                    <a href="programing.php" class="menu-btn">
                        PROGRAMING
                        <i class="fa-solid fa-code"></i>
                    </a>
                </div>
            </div>
            <iframe id="song" width="0" height="0"
            src="https://www.youtube.com/embed/5LrLW-T7D-w?enablejsapi=1&autoplay=1&mute=0&controls=0&loop=1&playlist=5LrLW-T7D-w&si=jY1LUv2wtgFb"
            title="YouTube video player" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen></iframe>
            <button class="music-toggle-btn tBOff">
                <i class="fa-solid fa-heart"></i>
            </button>
        </div>
        <script>
            function adjustScale() {
                const container = document.querySelector('.container');
                if (!container) 
                    return;
                
                let containerWidth,
                    containerHeight;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                if (windowWidth <= 768) {
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