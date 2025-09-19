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

            .list_for_content{
                position: absolute;
                top: 111px;
                left: 391px;
                width: 1053px;
                height: 655px;
                overflow-y: auto;
            }

            .list_for_content::-webkit-scrollbar{
                width: 0px;
            }

            .gallery_wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 0px;
                justify-content: flex-start;
                width: 100%;
            }
            
            .list_for_gallery {
                position: relative;
                width: calc(100% / 4);
                box-sizing: border-box;
            }

            .list_for_gallery:not(:nth-child(4n + 1)) {
                margin-left: -2px; 
            }
            .list_for_gallery:nth-child(n + 5) {
                margin-top: -2px;
            }

            .list_for_thum{
                width: 100%;
                aspect-ratio: 4/5;
                flex-shrink: 0;
                background: #ffffffff;
                border: #1B4CDB 2px solid;
            }

            .list_for_title{
                position: absolute;
                top: 20px;
                left: 20px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: "Tinos", "Noto Sans KR"; 
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            sideBar{
                position: absolute;
                top:113px;
                width: 391px;
                height: 656px;
                flex-shrink: 0;
                background: url("assets/images/2-for-image1.png") lightgray 50% / cover no-repeat;
            }

            .index_search{
                position: absolute;
                left: 59px;
                top: 160px;
                width: 270px;
                height: 40px;
                flex-shrink: 0;
                border-radius: 30px;
                border: 2px solid #0B2673;
                background: #EBEBEB;
            }

            .iS_btn{
                position: absolute;
                top: 6px;
                left: 15px;
                width: 28px;
                height: 28px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                background: url("assets//images/100-icon-search-b.png") center center / cover no-repeat;
            }

            .sB_title{
                position: absolute;
                left: 83px;
                top: 50px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 80px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .sB_text{
                position: absolute;
                left: 39px;
                top: 84px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                transform: rotate(-90deg);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <main>
                <sideBar>
                    <a class="sB_title">For</a>
                    <a class="sB_text">Kategorie 1.</a>
                    <a href="search.php">
                        <div class="index_search">
                            <div class="iS_btn"></div>
                        </div>
                    </a>
                </sideBar>

                <div class="list_for_content">
                    <div class="gallery_wrapper">
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 1</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 2</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 3</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 4</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 5</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 6</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 7</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 8</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 9</a>
                        </div>
                        <div class="list_for_gallery">
                            <div class="list_for_thum"></div>
                            <a class="list_for_title">제목 Title 10</a>
                        </div>
                    </div>
                </div>
            </main>

            <header>
                <?php include 'header.php'; ?>
                <div class="list_header_line"></div>
                <a>For</a>
            </header>

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
            });

            window.addEventListener('resize', adjustScale);
        </script>
    </body>
</html>