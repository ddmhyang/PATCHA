<?php
require_once 'includes/db.php';
$board_type = 'for';
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

            .list_header_line {
                position: absolute;
                left: 923px;
                top: 62px;
                width: 335px;
                height: 2px;
                background: #1B4CDB;
            }

            header > a {
                position: absolute;
                top: 50px;
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

            .list_for_content {
                position: absolute;
                top: 112.5px;
                left: 391px;
                width: 1053px;
                height: 656px;
                overflow-y: auto;
            }

            .list_for_content::-webkit-scrollbar {
                width: 0;
            }

            .gallery_wrapper {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 0;
                justify-content: flex-start;
                width: 1049px;
                box-sizing: border-box;
            }

            .list_for_gallery {
                position: relative;
                box-sizing: border-box;
                border: #1B4CDB 2px solid;
            }   

            .list_for_gallery:not(:nth-child(4n + 1)) {
                margin-left: -2px;
            }
            .list_for_gallery:nth-child(n + 5) {
                margin-top: -1.81px;
            }

            .list_for_thum {
                aspect-ratio: 4/5;
                width: 260.75px;
                flex-shrink: 0;
                background: #ffffffff;
                box-sizing: border-box;
            }

            .list_for_title {
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

            sideBar {
                position: absolute;
                top: 113px;
                width: 391px;
                height: 656px;
                flex-shrink: 0;
                background: url("assets/images/2-for-image1.png") lightgray 50% / cover no-repeat;
                box-sizing: border-box;
            }

            .index_search {
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

            .iS_btn {
                position: absolute;
                top: 6px;
                left: 15px;
                width: 28px;
                height: 28px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                background: url("assets//images/100-icon-search-b.png") center center / cover no-repeat;
            }

            .sB_title {
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

            .sB_text {
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

            .write-button {
                background-color: #1B4CDB;
                color: white;
                padding: 4px 12px;
                text-decoration: none;
                border-radius: 4px;
                position: absolute;
                top: 250px;
                right: 60px;
                font-size: 16px;
            }
            .login-menu{
                position: absolute;
                right: 17px;
                top: 20px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }
            
            .login-menu > a:visited {
                color: inherit;
            }
        </style>
        <div class="content">
            <main>
                <sidebar>
                    <a class="sB_title">For</a>
                    <a class="sB_text">Kategorie 1.</a>
                    <a href="#!search.php">
                        <div class="index_search">
                            <div class="iS_btn"></div>
                        </div>
                    </a>

                    <div class="login-menu">
                        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <a href="/actions/logout.php" title="로그아웃">Logout</a>
                    <?php else: ?>
                        <a href="#!login.php" title="로그인">Login</a>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div style="text-align: right; margin-top: 20px;">
                        <a
                            href="actions/upload_post.php?board=<?php echo $board_type; ?>"
                            class="write-button">글쓰기</a>
                    </div>
                    <?php endif; ?>
                </sidebar>

                <div class="list_for_content">
                    <div class="gallery_wrapper">
                        <?php while ($post = $result->fetch_assoc()): ?>

                        <?php
                        $link_href = $post['is_secret']
                            ? "#!page_secret.php?board={$board_type}&id={$post['id']}"
                            : "#!list_page_{$board_type}.php?id={$post['id']}";
                        ?>

                        <a
                            href="#!list_page_for.php?id=<?php echo $post['id']; ?>"
                            class="list_for_gallery">
                            <div class="list_for_thum">
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
                            <div class="list_for_title">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </div>
                        </a>
                        <?php endwhile; ?>
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