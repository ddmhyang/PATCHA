<?php
session_start();
include_once 'db.php';
include 'header.php';
?>

<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF--8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CATALYS</title>
        <style>
            body,
            html {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                background-color: #0B2673;
                overflow: hidden;
                position: relative;
                visibility: hidden;
            }

            .container {
                width: 1440px;
                height: 810px;
                flex-shrink: 0;
                background-size: cover;
                background-color: #ffffff;
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
            }

            a{
                white-space: nowrap;
                text-decoration: none;
            }

            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }

            footer{
                position: absolute;
                bottom: 41px;
            }

            .list_header_line{
                position: absolute;
                left: 923px;
                top: 62px;
                width: 335px;
                height: 2px;
                background: #1B4CDB;
            }

            header > a{
                position: absolute;
                top: 55px;
                left: 1270px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .list_etc_content{
                position: absolute;
                top: 79px;
                left: 529px;
                width: 624px;
                height: 631px;
                overflow-y: auto;
            }

            .list_etc_content::-webkit-scrollbar{
                width: 0px;
            }

            .gallery_wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 50px 10px;
                justify-content: flex-start;
                width: 100%;
            }
            
            .list_etc_gallery {
                width: calc((100% - 10px) / 2);
                position: relative;
                box-sizing: border-box;
            }

            .list_etc_thum{
                width: 100%;
                aspect-ratio: 307/427;
                flex-shrink: 0;
                border-radius: 10px;
                background: #ccccccff;
            }

            .list_etc_title{
                position: absolute;
                top: 49px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: "Tinos", "Noto Sans KR"; 
                font-size: 120px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .list_etc_date{
                position: absolute;
                top: 436px;
                left: 230px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;

            }

            .lLimg1{
                position: absolute;
                top: 107px;
                left: 0px;
                width: 508px;
                height: 571px;
                flex-shrink: 0;
                aspect-ratio: 508/571;
            }

            .lLimg2{
                position: absolute;
                top: 6px;
                right: 0px;
                width: 276px;
                height: 763px;
                flex-shrink: 0;
                aspect-ratio: 276/763;
            }

            .list_etc_pagenation{
                position: absolute;
                top: 727px;
                right: 287.5px;
                display: flex;
                flex-direction: row;
                gap: 12.5px;
            }

            .list_etc_pagenation > a{
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                text-align: center;
            }

            .list_etc_pagenation > a.on{
                background-color: #D9D9D9;
                width: 22px;
                height: 22px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                border-radius: 22px;
                text-align: center;
            }

            .nav{
                position: absolute;
                right: 118px;
                top: 550px;
                width: 50px;
                height: 145px;
                flex-shrink: 0;
                border-radius: 30px;
                background: #1B4CDB;
            }

            .nav_index_btn{
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 31px;
                width: 29px;
                height: 24px;
                flex-shrink: 0;
                aspect-ratio: 29/24;
                background: url('assets/images/100-icon-home-w.png') center center / cover no-repeat;
            }

            .nav_search_btn{
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 90px;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
                aspect-ratio: 24/24;
                background: url('assets/images/100-icon-search-w.png') center center / cover no-repeat;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <div class="list_header_line"></div>
                <a>log</a>
            </header>

            <main>
                <img class="lLimg1" src="assets/images/2-logpage-image1.png">
                <img class="lLimg2" src="assets/images/2-etcpage-image2.png">

                <div class="list_etc_content">

                    <div class="gallery_wrapper">
                        <div class="list_etc_gallery">
                            <div class="list_etc_thum"></div>
                            <a class="list_etc_date">2025 . 08</a>
                        </div>
                    </div>
                </div>
                <div class="list_etc_pagenation">
                    <a><</a>
                    <a>1</a>
                    <a>2</a>
                    <a>3</a>
                    <a>4</a>
                    <a>5</a>
                    <a>></a>
                </div>

                <div class="nav">
                    <a href="index.php" class="nav_index_btn"></a>
                    <a href="search.php" class="nav_search_btn"></a>
                </div>
            </main>

            <footer><?php include 'footer.php'; ?></footer>
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
                    containerHeight = 810;
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
                const imageElement = document.querySelector('.list_etc_pagenation a');
                if (imageElement) {
                    imageElement.classList.add('on');
                }
            });

            window.addEventListener('resize', adjustScale);
        </script>
    </body>
</html>