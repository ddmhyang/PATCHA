<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
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

            .list_sp_content{
                position: absolute;
                top: 167px;
                left: 50%;
                transform: translateX(-50%);
                width: 1186px;
                height: 657px;
            }
            .list_sp_gallery {
                position: relative;
            }

            .gallery_wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-start;
            }

            .list_sp_thum{
                width: 390px;
                height: 490px;
                flex-shrink: 0;
                border-radius: 15px;
                background: #B5B5B5;
            }


            .list_sp_title{
                position: absolute;
                top: 27px;
                left: 279px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: "Tinos", "Noto Sans KR"; 
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .list_sp_pagenation{
                position: absolute;
                top: 538px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                flex-direction: row;
                gap: 12.5px;
            }

            .list_sp_pagenation_bg{
                width: 22px;
                height: 22px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                fill: #D9D9D9;
            }

            .list_sp_pagenation > a{
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

            .list_sp_pagenation > a.on{
                background-color: #D9D9D9;
                width: 22px;
                height: 22px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                border-radius: 22px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <main>
                <div class="list_sp_content">
                    
                    <div class="gallery_wrapper">
                        <div class="list_sp_gallery">
                            <div class="list_sp_thum"></div>
                            <a class="list_sp_title">제목 Title</a>
                        </div>

                        <div class="list_sp_gallery">
                            <div class="list_sp_thum"></div>
                            <a class="list_sp_title">제목 Title</a>
                        </div>
                        
                        <div class="list_sp_gallery">
                            <div class="list_sp_thum"></div>
                            <a class="list_sp_title">제목 Title</a>
                        </div>
                        
                    </div>
                    <div class="list_sp_pagenation">
                        <a><</a>
                        <a>1</a>
                        <a>2</a>
                        <a>3</a>
                        <a>4</a>
                        <a>5</a>
                        <a>></a>
                    </div>
                </div>
            </main>
            
            <header>
                <?php include 'header.php'; ?>
                <div class="list_header_line"></div>
                <a>SP</a>
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
                const imageElement = document.querySelector('.list_sp_pagenation a');
                if (imageElement) {
                    imageElement.classList.add('on');
                }
            });

            window.addEventListener('resize', adjustScale);
        </script>
    </body>
</html>