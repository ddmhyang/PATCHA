<?php
require_once 'includes/db.php';
$board_type = 'log'; // 이 부분만 다릅니다.
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

            .list_log_content{
                position: absolute;
                top: 79px;
                left: 377px;
                width: 775px;
                height: 631px;
                overflow-y: auto;
            }

            .list_log_content::-webkit-scrollbar{
                width: 0px;
            }

            .gallery_wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-start;
                width: 100%;
                flex-direction: column;
            }
            
            .list_log_gallery {
                position: relative;
                box-sizing: border-box;
            }

            .list_log_thum{
                margin-left: 151px;
                width: 624px;
                height: 205px;
                flex-shrink: 0;
                border-radius: 10px;
                background: #ccccccff;
            }

            .list_log_title{
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

            .list_log_date{
                position: absolute;
                top: 175px;
                color: #000;
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

            .list_log_pagenation{
                position: absolute;
                top: 727px;
                right: 287.5px;
                display: flex;
                flex-direction: row;
                gap: 12.5px;
            }

            .list_log_pagenation > a{
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

            .list_log_pagenation > a.on{
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

                <div class="list_log_content">
                    <div class="gallery_wrapper">
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <a href="list_page_log.php?id=<?php echo $post['id']; ?>" class="list_log_gallery">
                                <div class="list_log_thum">
                                    <?php
                                        preg_match('/<img[^>]+src="([^">]+)"/', $post['content'], $matches);
                                        $thumbnail = $matches[1] ?? 'assets/images/2-logpage-image1.png';
                                    ?>
                                    <img src="<?php echo $thumbnail; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                </div>
                                <div class="list_log_title">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                    <span class="list_log_date"><?php echo date('Y.m.d', strtotime($post['created_at'])); ?></span>
                                </div>
                            </a>
                        <?php endwhile; ?>
                        <?php if ($result->num_rows === 0): ?>
                            <p style="text-align:center; width:100%; color: #888;">게시글이 없습니다.</p>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div style="text-align: right; margin-top: 20px;">
                        <a href="actions/upload_post.php?board=<?php echo $board_type; ?>" class="write-button" style="padding: 8px 15px; background-color: #333; color: white; text-decoration: none; border-radius: 4px;">글쓰기</a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="list_log_pagenation">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'on' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
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
                const imageElement = document.querySelector('.list_log_pagenation a');
                if (imageElement) {
                    imageElement.classList.add('on');
                }
            });

            window.addEventListener('resize', adjustScale);
        </script>
    </body>
</html>