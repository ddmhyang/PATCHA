<?php
require_once 'includes/db.php';
$board_type = 'sp';
$table_name = 'posts_' . $board_type;
$posts_per_page = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;
$total_posts_sql = "SELECT COUNT(*) FROM {$table_name}";
$total_result = $conn->query($total_posts_sql);
$total_posts = $total_result->fetch_row()[0];
$total_pages = ceil($total_posts / $posts_per_page);
$sql = "SELECT id, title, content, thumbnail, created_at, is_secret FROM {$table_name} ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $posts_per_page, $offset);
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
                justify-content: center;
                gap: 12.5px;
                align-items: center;
            }

            .list_sp_pagenation > a{
                justify-content: center;
                display: flex;
                align-items: center;
                width: 22px;
                height: 22px;
                border-radius: 22px;
                text-decoration: none;
                
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
                flex-shrink: 0;
                aspect-ratio: 1/1;
                text-align: center;
            }

            .write-button{
                background-color: #1B4CDB;
                color: white;
                padding: 4px 12px; 
                text-decoration: none; 
                border-radius: 4px;
                position: absolute;
                top: 530px;
                right: 0px;
                font-size: 16px;
            }
            
            .login-menu{
                position: absolute;
                right: 17px;
                top: 80px;
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
                <div class="list_sp_content">
                    
                    <div class="gallery_wrapper">
                        <?php while ($post = $result->fetch_assoc()): ?>

                            <?php
                            $link_href = $post['is_secret']
                                ? "#!page_secret.php?board={$board_type}&id={$post['id']}"
                                : "#!list_page_{$board_type}.php?id={$post['id']}";
                            ?>

                            <a href="#!list_page_sp.php?id=<?php echo $post['id']; ?>" class="list_sp_gallery">
                                <div class="list_sp_thum">
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
                            </a>
                        <?php endwhile; ?>
                    </div>

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div style="text-align: right; margin-top: 20px;">
                        <a href="actions/upload_post.php?board=<?php echo $board_type; ?>" class="write-button">글쓰기</a>
                    </div>
                    <?php endif; ?>

                    <div class="list_sp_pagenation">
                        <a><</a>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="#!list_sp.php?page=<?php echo $i; ?>" class="<?php echo ($page == $i) ? 'on' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        <a>></a>
                    </div>
                </div>
            </main>
            
            <header>
                <?php include 'header.php'; ?>
                <div class="list_header_line"></div>
                <a>SP</a>


                <div class="login-menu">
                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="/actions/logout.php" title="로그아웃">Logout</a>
                <?php else: ?>
                    <a href="#!login.php" title="로그인">Login</a>
                    <?php endif; ?>
                </div>
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