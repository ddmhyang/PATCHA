<?php
require_once 'includes/db.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$board_type = 'for';
$table_name = 'posts_' . $board_type;

if ($post_id <= 0) {
    die('잘못된 접근입니다.');
}

$sql = "SELECT * FROM {$table_name} WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die('게시글이 존재하지 않습니다.');
}
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

            .for_content{
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

            .for_content::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .for_btn1{
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

            .for_btn2{
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
            

            .list_page_for_title{
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

            .list_page_for_date{
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
        <div class="content">
            <header>
                <?php include 'header.php'; ?>
                

                <div class="list_page_for_title"><?php echo htmlspecialchars($post['title']); ?></div>
                <div class="list_page_for_date"><?php echo date('Y.m.d H:i', strtotime($post['created_at'])); ?></div>

            </header>

            <main>
                <div class="list_page_for">
                    <a href="list_for.php" class="for_btn1">For</a>
                    <a href="list_for.php" class="for_btn2">
                        Life is a journey of constant<br>
                        learning, loving, and growing,<br>
                        where every experience shapes us into stronger,<br>
                        wiser versions.
                    </a>
                    <div class="for_content">
                        <?php echo $post['content']; ?>
                    </div>

                    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div class="admin-buttons" style="text-align: right; margin: 20px 0; display:flex; justify-content: flex-end; gap: 10px;">
                        <a href="actions/upload_post.php?board=<?php echo $board_type; ?>&id=<?php echo $post['id']; ?>" style="padding: 5px 10px; background-color: #444; color:white; text-decoration:none;">수정</a>
                        <a href="actions/delete_post.php?board=<?php echo $board_type; ?>&id=<?php echo $post['id']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" style="padding: 5px 10px; background-color: #d9534f; color:white; text-decoration:none;">삭제</a>
                    </div>
                    <?php endif; ?>
                </div>
            </main>

            <footer>
                <?php include 'footer.php'; ?>
            </footer>
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