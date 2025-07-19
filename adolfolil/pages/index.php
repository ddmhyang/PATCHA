<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DolfoLil</title>
        <link rel="icon" type="image/png" href="assets/img/logo1.jpg">
        <style>
            @font-face {
                font-family: 'DungGeunMo';
                src: url("assets/fonts/DungGeunMo.ttf") format('truetype');
            }
            @font-face {
                font-family: 'Galmuri9';
                src: url("assets/fonts/Galmuri9.ttf") format('truetype');
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
                font-family: 'Galmuri9', sans-serif;
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
                background: url("assets/img/background.png") rgb(0, 0, 0) 50% / cover no-repeat;
                overflow: hidden;
                box-sizing: border-box;
            }
            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }

            .index_border{
                display: flex;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 749px;
                height: 567px;
                flex-shrink: 0;
                background: #1A1A1A;
            }

            .index_box{
                display: flex;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 727px;
                height: 537px;
                flex-shrink: 0;
                background: url("assets/img/object2.png") center center  no-repeat;
            }

            .index_top{
                display: flex;
                position: relative;
                top: 0px;
                left: 0px;
                width: 749px;
                height: 66px;
                flex-shrink: 0;
                background: #1A1A1A;
            }

            .index_chang{
                display: flex;
                position: relative;
                top: 18px;
                left: 445px;
            }

            .index_title{
                display: flex;
                position: relative;
                top: 18px;
                left: 23px;
                color: #FAFAFA;
                font-family: 'DungGeunMo';
                font-size: 32px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                text-decoration-line: none;
            }

            .index_play{
                display: flex;
                position: absolute;
                left: 50%;
                top: 183px;
                transform: translateX(-50%);
                width: 213px;
                height: 213px;
                flex-shrink: 0;
                fill: rgba(250, 250, 250, 0.80);
                cursor: pointer;
                transition: transform 0.2s ease-in-out;
            }

            .index_play:hover{
                transform: scale(1.05) translateX(-47%);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="index_border">
                <div class="index_box"></div>
                <div class="index_top">
                    <a href="index.html" class="index_title">DolfoLil</a>
                    <svg class="index_chang" xmlns="http://www.w3.org/2000/svg" width="150" height="30" viewBox="0 0 150 30" fill="none">
                    <rect width="30" height="30" fill="#FAFAFA"/>
                    <rect x="60" width="30" height="30" fill="#FAFAFA"/>
                    <rect x="120" width="30" height="30" fill="#FAFAFA"/>
                    </svg>
                </div>
                <svg class="index_play" xmlns="http://www.w3.org/2000/svg" width="155" height="179" viewBox="0 0 155 179" fill="none">
                <path d="M152.5 85.1699C155.833 87.0944 155.833 91.9056 152.5 93.8301L7.75001 177.402C4.41666 179.326 0.250007 176.92 0.250007 173.071L0.250014 5.92853C0.250014 2.07953 4.41669 -0.326085 7.75001 1.59842L152.5 85.1699Z" fill="#FAFAFA" fill-opacity="0.8"/>
                </svg>
            </div>
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

            const indexPlay = document.querySelector('.index_play')
            indexPlay.addEventListener('click', () => {
                window.location.href = 'pages/main.php';
            });
        </script>
    </body>
</html>