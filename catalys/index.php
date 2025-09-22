<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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

            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
            }
            
            a{
                white-space: nowrap;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
    <div class="container">
            <main>
                <div id="app-content"></div>
            </main>
        </div>
        <script>
            function adjustScale() {
                const container = document.querySelector('.container');
                if (!container) return;
                let containerWidth = 1440, containerHeight = 810;
                const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
                const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
                container.style.transform = `scale(${scale})`;
                container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
                container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
            }
            window.addEventListener('load', () => {
                adjustScale();
                document.body.style.visibility = 'visible';
            });
            window.addEventListener('resize', adjustScale);

            $(document).ready(function() {
                const content = $('#app-content');
                function loadPage(page) {
                    if (!page) {
                        page = 'main.php';
                    }
                    
                    content.load('/' + page, function(response, status, xhr) {
                        if (status == "error") {
                            content.html('<p style="text-align:center; padding: 50px;">페이지를 불러올 수 없습니다: ' + xhr.statusText + '</p>');
                        }
                    });
                }

                $(window).on('hashchange', function() {
                    let page = window.location.hash.substring(2); 
                    loadPage(page);
                });

                if (window.location.hash) {
                    $(window).trigger('hashchange');
                } else {
                    loadPage('main.php');
                }

                $(document).on('click', 'a[data-spa]', function(e) {
                    e.preventDefault();
                    let page = $(this).attr('href');
                    window.location.hash = page.substring(1); 
                });

                $(document).on('click', '.index_search', function(e) {
                    e.preventDefault(); 
                    const searchQuery = $(this).siblings('input[type="text"]').val(); 
                    if (searchQuery) {
                        window.location.hash = '#!search.php?query=' + encodeURIComponent(searchQuery);
                    } else {
                        window.location.hash = '#!search.php';
                    }
                });
            });
        </script>
    </body>
</html>