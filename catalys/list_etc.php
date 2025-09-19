<?php
require_once 'includes/db.php';
$board_type = 'etc'; // 이 부분만 다릅니다.
$table_name = 'posts_' . $board_type;
$posts_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;
$total_posts_sql = "SELECT COUNT(*) FROM {$table_name}";
$total_result = $conn->query($total_posts_sql);
$total_posts = $total_result->fetch_row()[0];
$total_pages = ceil($total_posts / $posts_per_page);
$sql = "SELECT * FROM {$table_name} ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $posts_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
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
                height: 100%;
                width: 100%;
                aspect-ratio: 307/427;
                flex-shrink: 0;
                border-radius: 10px;
                background: #ccccccff;
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
                top: 491px;
                width: 50px;
                height: 204px;
                flex-shrink: 0;
                border-radius: 30px;
                background: #1B4CDB;
            }

            .nav_index_btn{
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 90px;
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
                top: 149px;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
                aspect-ratio: 24/24;
                background: url('assets/images/100-icon-search-w.png') center center / cover no-repeat;
            }

            /* 1. 제공해주신 원래 코드 (수정할 필요 없음) */
            .login-menu {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 31px;
                width: 24px;
                height: 24px;
                background: url('assets/images/user.png') center center / cover no-repeat;
                cursor: pointer;
                /* flex-shrink와 aspect-ratio는 width, height가 고정되어 있으므로 필수는 아닙니다. */
            }

            /* 2. 아래 코드를 style.css에 추가해주세요 */
            .login-menu a {
                display: block; /* a 태그를 블록 요소로 만들어 크기를 가질 수 있게 함 */
                width: 100%;    /* 부모(div)의 너비를 꽉 채움 */
                height: 100%;   /* 부모(div)의 높이를 꽉 채움 */
            }

            .write-button{
                background-color: #1B4CDB;
                color: white;
                padding: 4px 12px; 
                text-decoration: none; 
                border-radius: 4px;
                position: absolute;
                bottom: 96px;
                left: 405px;
                font-size: 16px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <div class="list_header_line"></div>
                <a>etc</a>
            </header>

            <main>
                <img class="lLimg1" src="assets/images/2-logpage-image1.png">
                <img class="lLimg2" src="assets/images/2-etcpage-image2.png">

                <div class="list_etc_content">

                    <div class="gallery_wrapper">
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <a href="list_page_etc.php?id=<?php echo $post['id']; ?>" class="list_etc_gallery">
                                <div class="list_etc_thum">
                                    <?php
                                    // 1. 직접 업로드한 썸네일이 있는지 확인
                                    if (!empty($post['thumbnail'])) {
                                        echo '<img src="' . htmlspecialchars($post['thumbnail']) . '" alt="' . htmlspecialchars($post['title']) . '" style="width:100%; height:100%; object-fit:cover;">';
                                    } else {
                                        // 2. 썸네일이 없으면 본문에서 첫 번째 이미지를 찾음
                                        preg_match('/<img[^>]+src="([^">]+)"/', $post['content'], $matches);
                                        if (isset($matches[1]) && !empty($matches[1])) {
                                            echo '<img src="' . htmlspecialchars($matches[1]) . '" alt="' . htmlspecialchars($post['title']) . '" style="width:100%; height:100%; object-fit:cover;">';
                                        } else {
                                            // 3. 본문에도 이미지가 없으면 #eee 배경 표시
                                            echo '<div style="width: 100%; height: 100%; background-color: #eee;"></div>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="list_etc_title">
                                    <span class="list_etc_date"><?php echo date('Y.m', strtotime($post['created_at'])); ?></span>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>

                </div>
                <div class="nav">

                <div class="login-menu">
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <a href="/actions/logout.php" title="로그아웃"></a>
                    <?php else: ?>
                        <a href="/login.php" title="로그인"></a>
                    <?php endif; ?>
                </div>

                    <a href="index.php" class="nav_index_btn"></a>
                    <a href="search.php" class="nav_search_btn"></a>
                </div>
            </main>

            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <div style="text-align: right; margin-top: 20px;">
                <a href="actions/upload_post.php?board=<?php echo $board_type; ?>" class="write-button">글쓰기</a>
            </div>
            <?php endif; ?>

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