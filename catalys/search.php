<?php
require_once 'includes/db.php';

$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];

if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";

    // 4개 테이블을 UNION ALL로 묶어 한번에 검색
    $sql = "
        (SELECT id, title, created_at, 'cp' as board_type FROM posts_cp WHERE title LIKE ? OR content LIKE ?)
        UNION ALL
        (SELECT id, title, created_at, 'for' as board_type FROM posts_for WHERE title LIKE ? OR content LIKE ?)
        UNION ALL
        (SELECT id, title, created_at, 'log' as board_type FROM posts_log WHERE title LIKE ? OR content LIKE ?)
        UNION ALL
        (SELECT id, title, created_at, 'sp' as board_type FROM posts_sp WHERE title LIKE ? OR content LIKE ?)
        ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    // s: string, 총 8개의 파라미터
    $stmt->bind_param('ssssssss', $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $results[] = $row;
    }
}
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
                background-color: #0B2673;
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

            .search_bar_icon{
                position: absolute;
                left: 446px;
                top: 390px;
                width: 30px;
                height: 30px;
                flex-shrink: 0;
                aspect-ratio: 1/1;
                background: url("assets//images/100-icon-search-w.png") center center / cover no-repeat;
                cursor: pointer;
                border: none;
                padding: 0px;
            }

            .search_bar_in{
                position: absolute;
                left: 484px;
                top: 370px;
                width: 468px;
                height: 50px;
                background: #ffffff00;
                font-size: 16px;
                padding: 0px;
                margin: 0px;
                border: none;
                color: #fff;
            }

            .search_bar_in:focus {
                outline: none;
            }


            .search_bar_line{
                position: absolute;
                left: 486px;
                top: 420px;
                width: 468px;
                height: 2px;
                background: #FFF;
            }


            .search_result{
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
                top: 450px;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <form class="search_bar" action="search.php" method="get">
                <input class="search_bar_in" type="text" name="query" value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="search_bar_icon">
                </button>
                <div class="search_bar_line"></div>
            </form>

            <div class="search_result">
                <h3>'<?= htmlspecialchars($search_query) ?>'에 대한 검색 결과 (총 <?= count($results) ?>개)</h3>
                
                <?php if (count($results) > 0): ?>
                    <ul>
                        <?php foreach ($results as $item): ?>
                            <li>
                                <a href="list_page_<?= $item['board_type'] ?>.php?id=<?= $item['id'] ?>">
                                    <strong>[게시판: <?= strtoupper($item['board_type']) ?>]</strong>
                                    <?= htmlspecialchars($item['title']) ?>
                                    <span style="font-size: 0.8em; color: #888;"><?= date('Y.m.d', strtotime($item['created_at'])) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>검색 결과가 없습니다.</p>
                <?php endif; ?>
            </div>
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