<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Intro & About Me - My Mix-Match Archive</title>
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
                    <i class="fa-solid fa-user-astronaut floating-icon fi-1"></i>
                    <i
                        class="fa-solid fa-heart floating-icon fi-2"
                        style="left: 160px; bottom: 50px; color: var(--accent-pink);"></i>

                    <div class="sub-title">Who am I?</div>
                    <h1>About Me.</h1>
                    <a href="main.html" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        메인으로 돌아가기</a>
                </div>

                <div class="right-section-content">
                    <div class="profile-card">
                        <i class="fa-solid fa-heart deco-heart dh-1"></i>
                        <i class="fa-solid fa-heart deco-heart dh-2"></i>

                        <div class="profile-img-box">
                        </div>
                        <h2 class="profile-name">DdmHyang</h2>
                        <p class="profile-bio">
                            게임 개발 / 소설 / 그림 / 홈페이지 제작
                        </p>

                        <div class="likes-section">
                            <h3 class="likes-title">Work area</h3>
                            <div class="tags-container">
                                <a href="https://x.com/d_dmhyang" class="like-tag">twitter (X)</a>
                                <a href="https://kre.pe/jesA" class="like-tag">crepe</a>
                                <a href="https://www.postype.com/@ddmhyang" class="like-tag">Postype</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
        </script>

    </body>
</html>