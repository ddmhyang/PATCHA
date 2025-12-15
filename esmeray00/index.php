<?php
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>title</title>
        <style>
            @font-face {
                font-family: 'MyMixFont';
                src: url("assets/fonts/LibreBodoni-VariableFont_wght.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
                unicode-range: U+0020-007E;
            }
            @font-face {
                font-family: 'MyMixFont';
                src: url("assets/fonts/KoPubWorld Batang Bold.ttf") format('truetype');
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
            }

            .container{
                width: 720px;
                height: 1280px;
                flex-shrink: 0;
                aspect-ratio: 16/9;
                background-size: cover;
                background-color: #000000;
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
                font-family: 'MyMixFont';
                background: url("/assets/images/background.png") no-repeat center center;
                color:#7078A7;
                transition: background-color 1s ease-in-out;
            }
            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }

            .index_button{
                width: 310px;
                height: 364px;
                flex-shrink: 0;
                aspect-ratio: 310/364;
                background: url("/assets/images/letter.png") no-repeat center center;
                background-size: cover;
                position: absolute;
                top: 458px;
                left: 205px;
                cursor: pointer;
            }
            
            .index_title{
                position: absolute;
                top: 28px;
                left: 94px;
                font-family: "MyMixFont";
                color: #A10000;
                font-weight: bold;
                font-size: 20px;
            }

            .index_bar{
                position: absolute;
                top: 58px;
                left: 42px;
                width: 234px;
                height: 1px;
                background-color: black;
            }

            .index_content{
                position: absolute;
                width: 234px;
                font-family: "MyMixFont";
                left: 42px;
                font-weight: bold;
                font-size: 16px;
                top: 65px;
                color: black;
            }

        </style>
    </head>
    <body>
        
        <div class="container">
            <div class="index_button" onclick="location.href='pages/main.php#/'">
                <a class="index_title">INTERFERE</a>
                <div class="index_bar"></div>
                <a class="index_content">> (v.) take part or intervene in an activity without invitation or necessity.</a>
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

                containerWidth = 720;
                containerHeight = 1280;

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