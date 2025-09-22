<?php
require_once 'includes/db.php';
$board_type = 'etc';
$table_name = 'posts_' . $board_type;
$sql = "SELECT id, title, content, thumbnail, created_at, is_secret FROM {$table_name} ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
        <style>
            .content {
                width: 1440px;
                height: 810px;
                flex-shrink: 0;
                background-size: cover;
                background-color: #ffffff;
                transform-origin: top left;
                position: absolute;
                transition: background-color 1s ease-in-out;
                font-family: "Tinos", "Noto Sans KR";
            }
                    
            a{
                white-space: nowrap;
                text-decoration: none;
            }
            footer {
                position: absolute;
                bottom: 41px;
            }

            .list_header_line{
                position: absolute;
                left: 768px;
                top: 60px;
                width: 335px;
                height: 2px;
                background: #1B4CDB;
            }

            header > a{
                position: absolute;
                top: 48px;
                left: 1115px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 20px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .list_etc_content {
                position: absolute;
                top: 79px;
                left: 529px;
                width: 624px;
                height: 631px;
                overflow-y: auto;
            }

            .list_etc_content::-webkit-scrollbar {
                width: 0;
            }

            .gallery_wrapper {
                display: flex;
                flex-wrap: wrap;
                gap: 50px 10px;
                justify-content: flex-start;
                width: 100%;
                height: 100%;
            }

            .list_etc_gallery {
                width: calc((100% - 10px) / 2);
                position: relative;
                box-sizing: border-box;
            }

            .list_etc_thum {
                height: 100%;
                width: 100%;
                aspect-ratio: 307/427;
                flex-shrink: 0;
                border-radius: 10px;
                background: #ccccccff;
            }

            .list_etc_date {
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

            .lLimg1 {
                position: absolute;
                top: 0px;
                left: 73px;
                width: 370px;
                height: 810px;
                flex-shrink: 0;
            }

            .lLimg2 {
                position: absolute;
                top: 6px;
                right: 0;
                width: 276px;
                height: 763px;
                flex-shrink: 0;
                aspect-ratio: 276/763;
            }

            .nav {
                position: absolute;
                right: 118px;
                top: 491px;
                width: 50px;
                height: 204px;
                flex-shrink: 0;
                border-radius: 30px;
                background: #1B4CDB;
            }

            .nav_index_btn {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 90px;
                width: 29px;
                height: 24px;
                flex-shrink: 0;
                aspect-ratio: 29/24;
                background: url("assets/images/100-icon-home-w.png") center center / cover no-repeat;
            }

            .nav_search_btn {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 149px;
                width: 24px;
                height: 24px;
                flex-shrink: 0;
                aspect-ratio: 24/24;
                background: url("assets/images/100-icon-search-w.png") center center / cover no-repeat;
            }

            .login-menu {
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 31px;
                width: 24px;
                height: 24px;
                background: url("assets/images/user.png") center center / cover no-repeat;
                cursor: pointer;
            }

            .login-menu a {
                display: block;
                width: 100%;
                height: 100%;
            }

            .write-button {
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
        <div class="content">
            <main>
                <img class="lLimg1" src="assets/images/2-etcpage-image1.png">
                <img class="lLimg2" src="assets/images/2-etcpage-image2.png">

                <div class="list_etc_content">

                    <div class="gallery_wrapper">
                        <?php while ($post = $result->fetch_assoc()): ?>

                        <?php
                        $link_href = $post['is_secret']
                            ? "#!page_secret.php?board={$board_type}&id={$post['id']}"
                            : "#!list_page_{$board_type}.php?id={$post['id']}";
                        ?>

                        <a
                            href="#!list_page_etc.php?id=<?php echo $post['id']; ?>"
                            class="list_etc_gallery">
                            <div class="list_etc_thum">
                            <?php
                                    if (!empty($post['thumbnail'])) {
                                        echo '<img src="' . htmlspecialchars($post['thumbnail']) . '" alt="' . htmlspecialchars($post['title']) . '" style="width:100%; height:100%; object-fit:cover;">';
                                    } else {
                                        preg_match('/<img[^>]+src="([^">]+)"/', $post['content'], $matches);
                                        if (isset($matches[1]) && !empty($matches[1])) {
                                            echo '<img src="' . htmlspecialchars($matches[1]) . '" alt="' . htmlspecialchars($post['title']) . '" style="width:100%; height:100%; object-fit:cover;">';
                                        } else {
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
                        <a href="#!login.php" title="로그인"></a>
                        <?php endif; ?>
                    </div>

                    <a href="#!main.php" class="nav_index_btn"></a>
                    <a href="#!search.php" class="nav_search_btn"></a>
                </div>
            </main>
            
            <header>
                <div class="list_header_line"></div>
                <a href="#!category.php">etc</a>
            </header>

            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <div style="text-align: right; margin-top: 20px;">
                <a
                    href="actions/upload_post.php?board=<?php echo $board_type; ?>"
                    class="write-button">글쓰기</a>
            </div>
            <?php endif; ?>

            <footer><?php include 'footer.php'; ?></footer>
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

                contentWidth = 1440;
                contentHeight = 810;

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