        <style>

            .category_btn{
                margin-top: 371px;
                margin-left: 697px;
                display: flex;
                flex-direction: column;
                gap:13px;
                color: #FFF;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .category_btn a{
                color: #FFF;
            }

            
        </style>
        <div class="content">
            <div class="category_btn">
                <a style="margin-bottom: 4px;">Menu</a>
                <a href="#list_log.php">·log</a>
                <a href="#list_etc.php">·ETC</a>
            </div>
        </div>
        <script>
            function adjustScale() {
                const content = document.querySelector('.content');
                if (!content) 
                    return;
                
                let contentWidth,
                    contentHeight;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                if (windowWidth <= 768) {
                    contentWidth = 720;
                    contentHeight = 1280;
                } else {
                    contentWidth = 1440;
                    contentHeight = 810;
                }

                const scale = Math.min(
                    windowWidth / contentWidth,
                    windowHeight / contentHeight
                );
                content.style.transform = `scale(${scale})`;
                content.style.left = `${ (windowWidth - contentWidth * scale) / 2}px`;
                content.style.top = `${ (windowHeight - contentHeight * scale) / 2}px`;

            }

            window.addEventListener('load', () => {
                adjustScale();
                document.body.style.visibility = 'visible';
            });

            window.addEventListener('resize', adjustScale);
        </script>