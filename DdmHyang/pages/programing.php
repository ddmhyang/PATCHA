<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Programing - My Mix-Match Archive</title>
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
                    <i class="fa-solid fa-code floating-icon fi-1"></i>
                    <i
                        class="fa-solid fa-gear floating-icon fi-2"
                        style="left: 170px; bottom: 60px;"></i>

                    <div class="sub-title">Category</div>
                    <h1>Programing</h1>
                    <p class="description">
                        따~~악!!!!<br>
                        <b>버그 하나만 더 고치고</b><br>
                        잔다 내가!!!!
                    </p>
                    <a href="main.html" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        메인으로 돌아가기</a>
                </div>

                <div class="right-section-content">
                    <ul class="prog-list">
                        <li class="prog-item">
                            <h3 class="prog-title">
                                <i class="fa-brands fa-unity"></i>
                                Unity 2D 플랫폼 게임</h3>
                            <p class="prog-desc">유니티 엔진과 C#을 활용하여 개발한 레트로풍 2D 액션 플랫포머 게임입니다.</p>
                            <div class="tech-stack">
                                <span class="tech-badge">Unity</span>
                                <span class="tech-badge">C#</span>
                                <span class="tech-badge">Game Dev</span>
                            </div>
                        </li>
                    </ul>
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