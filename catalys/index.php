
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

            .index_top_title{
                position: absolute;
                top: 47px;
                left: 1106px;
                cursor: pointer;
            }

            .index_top_title > svg{
                width: 217px;
                height: 47px;
                flex-shrink: 0;
                fill: #FFF;
                stroke-width: 2px;
                stroke: #1B4CDB;
            }

            .index_top_title > a{
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: #1B4CDB;
                font-family: Tinos;
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .index_title{
                position: absolute;
                top: 332px;
                left: 110px;
                width: 1208px;
                height: 144px;
                flex-shrink: 0;
                aspect-ratio: 151/18;
                background: url("assets/images/1-mainpage-image2.png") center center / cover no-repeat;
            }

            .index_top_text{
                position: absolute;
                top: 154px;
                left: 129px;
                flex-shrink: 0;
            }

            .iTT_X{
                position: absolute;
                color: #1B4CDB;
                text-align: right;
                leading-trim: both;
                text-edge: cap;
                font-family: "Playfair Display";
                font-size: 24px;
                font-style: normal;
                font-weight: 700;
                line-height: normal;
            }

            .iTT_id{
                position: absolute;
                top: 7px;
                left: 22px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .iTT_content{
                position: absolute;
                top: 35px;
                left: 26px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: "Noto Sans KR";
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: 130%;
            }

            .iTT_line{
                position: absolute;
                top: 35px;
                left: 5px;
                width: 4px;
                height: 50px;
                background: #1B4CDB;
            }

            .index_search{
                position: absolute;
                left: 136px;
                top: 260px;
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
                
            .index_category{
                position: absolute;
                top: 525px;
                left: 136px;
                color: #1B4CDB;
                font-family: Tinos;
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                display: flex;
                flex-direction: column;
                gap: 28px;
            }

            .iC_line{
                position: absolute;
                width: 2px;
                height: 98px;
                background: #1B4CDB;
            }

            .index_category > a{
                margin-left: 17px;
                color: #1B4CDB;
                font-family: Tinos;
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .index_background_img {
                position: absolute;
                top: 71px;
                left: 574px;
                width: 770px;
                height: 711px;
                flex-shrink: 0;
                background: url("assets/images/1-mainpage-image1.png") center center / cover no-repeat;
                transform-origin: left bottom;
                transform: scale(1);
                transition: transform 1s ease-out;
            }

            .index_background_img.loaded {
                transform: scale(0.9);
            }

            footer{
                position: absolute;
                bottom: 41px;
            }
            
        </style>
    </head>
    <body>
        <div class="container">
            <div class="index_top_title">
                <svg xmlns="http://www.w3.org/2000/svg" width="217" height="47" viewBox="0 0 217 47" fill="none">
                    <path d="M108.5 1C138.412 1 165.463 3.62678 185.01 7.86035C194.791 9.97889 202.639 12.4889 208.018 15.2432C210.708 16.6209 212.731 18.0344 214.068 19.4502C215.402 20.8615 216 22.2125 216 23.5C216 24.7875 215.402 26.1385 214.068 27.5498C212.731 28.9656 210.708 30.3791 208.018 31.7568C202.639 34.5111 194.791 37.0211 185.01 39.1396C165.463 43.3732 138.412 46 108.5 46C78.5877 46 51.5367 43.3732 31.9902 39.1396C22.209 37.0211 14.361 34.5111 8.98242 31.7568C6.29201 30.3791 4.26944 28.9656 2.93164 27.5498C1.59823 26.1385 1 24.7875 1 23.5C1 22.2125 1.59823 20.8615 2.93164 19.4502C4.26944 18.0344 6.29201 16.6209 8.98242 15.2432C14.361 12.4889 22.209 9.97889 31.9902 7.86035C51.5367 3.62678 78.5877 1 108.5 1Z" fill="white" stroke="#1B4CDB" stroke-width="2"/>
                </svg>
                <a>PERSONAL WEB PAGE</a>
            </div>

            <div class="index_top_text">
                <a class="iTT_X" href="https://x.com/sannoru12345"  style="text-decoration: none;">X</a>
                <a class="iTT_id" href="https://x.com/sannoru12345"  style="text-decoration: none;">@sannoru12345</a>
                <a class="iTT_content">
                    A substance that accelerates a chemical reaction<br>
                    without being consumed, or a person or event<br>
                    that precipitates significant change or action in a particular situation.
                </a>
                <div class="iTT_line"></div>
            </div>
            <a class="index_search" href="search.php" style="text-decoration: none;">
                <div class="iS_btn"></div>
            </a>

            <div class="index_title"></div>

            <div class="index_category">
                <div class="iC_line"></div>
                <a href="list_for.php" style="text-decoration: none;" class="iC_for">Kategorie 1. For</a>
                <a href="list_log.php" style="text-decoration: none;" class="iC_cp">Kategorie 2. CP</a>
                <a href="list_sp.php" style="text-decoration: none;" class="iC_sp">Kategorie 3. SP</a>
            </div>

            <footer><?php include 'footer.php'; ?></footer>
            
            <div class="index_background_img"></div>
            

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
                const imageElement = document.querySelector('.index_background_img');
                if (imageElement) {
                    imageElement.classList.add('loaded');
                }
            });


            window.addEventListener('resize', adjustScale);


            const indexTopTitle = document.querySelector('.index_top_title');
            indexTopTitle.addEventListener('click', () => {
                window.location.href = 'index.php';
            });
        </script>
    </body>
</html>