<?php
require_once 'includes/db.php';
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$board_type = 'log'; // board_type 변경
$table_name = 'posts_' . $board_type;
if ($post_id <= 0) die('잘못된 접근입니다.');
$sql = "SELECT * FROM {$table_name} WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
if (!$post) die('게시글이 존재하지 않습니다.');
?>


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
                display: flex;
                flex-direction: column;
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

            header {
                height: 112px; 
                flex-shrink: 0;
            }

            main {
                height: 657px; 
                overflow-y: auto;
                flex-shrink: 0;
            }

            footer {
                flex-grow: 1;
            }

            .log_content{
                position: absolute;
                top: 165px;
                left: 383px;
                width:676px;
                height: 570px; 
                color: #000;
                leading-trim: both;
                text-edge: cap;
                font-family: "Noto Sans KR";
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
                overflow-y: auto;
            }

            .log_content::-webkit-scrollbar {
                width: 0;
                height: 0;
            }


            .log_btn1{
                position: absolute;
                left: 1120px;
                top: 158px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 80px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .log_btn2{
                position: absolute;
                left: 1120px;
                top: 258px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: 16px;
            }

            black{
                color: black;
            }

            
            .list_page_log_title{
                position: absolute;
                top: 49px;
                left: 384px;
                color: #1B4CDB;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 24px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

            .list_page_log_date{
                position: absolute;
                left: 386px;
                top: 78px;
                color: #777;
                leading-trim: both;
                text-edge: cap;
                font-family: Tinos;
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
                line-height: normal;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <header>
                <?php include 'header.php'; ?>

                <div class="list_page_log_title"><?php echo htmlspecialchars($post['title']); ?></div>
                <div class="list_page_log_date"><?php echo date('Y.m.d H:i', strtotime($post['created_at'])); ?></div>
            </header>

            <main>
                <div class="list_page_log"></div>
                    <a href="list_log.php" class="log_btn1">log</a>
                    <a href="list_log.php" class="log_btn2">
                        <black>E</black>ach moment teaches us something new <br>
                        while <black>d</black>etermined spirits push through challenges. <br>
                        <black>O</black>ptimism guides us forward as resilience <br>
                        builds strength. <black>Y</black>earning for meaning, <br>
                        we move toward <black>o</black>ur destiny.
                    </a>
                    <div class="log_content">
                        <?php echo $post['content']; ?>
                    </div>

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div class="admin-buttons" style="text-align: right; margin: 20px 0; display:flex; justify-content: flex-end; gap: 10px;">
                        <a href="actions/upload_post.php?board=<?php echo $board_type; ?>&id=<?php echo $post['id']; ?>" style="padding: 5px 10px; background-color: #444; color:white; text-decoration:none;">수정</a>
                        <a href="actions/delete_post.php?board=<?php echo $board_type; ?>&id=<?php echo $post['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" style="padding: 5px 10px; background-color: #d9534f; color:white; text-decoration:none;">삭제</a>
                    </div>
                    <?php endif; ?>
                    <a href="list_for.php" class="list_page_log_back">back</a>
                </div>
            </main>

            <footer>
                <?php include 'footer.php'; ?>
            </footer>
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