<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Novel & Scenario - My Mix-Match Archive</title>
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
                <div class="deco-tape tape-2">World</div>

                <div class="left-section">
                    <i class="fa-solid fa-book-open floating-icon fi-1"></i>
                    <i
                        class="fa-solid fa-feather-pointed floating-icon fi-2"
                        style="left: 180px; bottom: 40px;"></i>

                    <div class="sub-title">Category</div>
                    <h1>Novel</h1>
                    <p class="description">
                        여긴 또 뭘 쓰지
                    </p>
                    <a href="main.html" class="back-btn">
                        <i class="fa-solid fa-arrow-left"></i>
                        메인으로 돌아가기</a>
                </div>

                <div class="right-section-content">
                    <ul class="novel-list">
                        <li class="novel-item">
                            <h3 class="novel-title">제목</h3>
                            <p class="novel-desc">한 줄 소개</p>
                            <div class="novel-tags">
                                <span class="tag">#SF</span>
                                <span class="tag">#BL</span>
                                <span class="tag">#리버시블</span>
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