<?php
require_once 'includes/db.php';

$search_query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];

if (!empty($search_query)) {
    $search_term = "%" . $search_query . "%";

    $sql = "
        (SELECT id, title, created_at, 'for' as board_type FROM posts_for WHERE title LIKE ? OR content LIKE ?)
        UNION ALL
        (SELECT id, title, created_at, 'log' as board_type FROM posts_log WHERE title LIKE ? OR content LIKE ?)
        UNION ALL
        (SELECT id, title, created_at, 'sp' as board_type FROM posts_sp WHERE title LIKE ? OR content LIKE ?)
        UNION ALL
        (SELECT id, title, created_at, 'etc' as board_type FROM posts_etc WHERE title LIKE ? OR content LIKE ?)
        ORDER BY created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('ssssssss', $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $results[] = $row;
        }
        $stmt->close();
    } else {
    }
}
$conn->close();
?>

        <style>
            .content {
                width: 1440px;
                height: 810px;
                flex-shrink: 0;
                background-size: cover;
                background-color: #0B2673;
                transform-origin: top left;
                position: absolute;
                transition: background-color 1s ease-in-out;
                font-family: "Tinos", "Noto Sans KR";
            }
                    
            a{
                white-space: nowrap;
                text-decoration: none;
            }
            a:visited {
                color: inherit;
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
                max-width: 500px;
                overflow: auto;
                height: 300px;
            }

            .search_result::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .search_results_list ul li a{
                color: #fff;
            }

            .search_results_list ul {
                list-style: none;
            }
        </style>
        <div class="content">
            <form class="search_bar" id="searchForm" action="search.php" method="get">
                <input class="search_bar_in" type="text" id="searchInput" name="query" value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit" class="search_bar_icon">
                </button>
                <div class="search_bar_line"></div>
            </form>

            <div class="search_result">
                <h3>'<?= htmlspecialchars($search_query) ?>'에 대한 검색 결과 (총 <?= count($results) ?>개)</h3>
                <div class="search_results_list">
                    <?php if (count($results) > 0): ?>
                        <ul>
                            <?php foreach ($results as $item): ?>
                                <li>
                                    <a href="#!list_page_<?= $item['board_type'] ?>.php?id=<?= $item['id'] ?>">
                                        <strong>[<?= strtoupper($item['board_type']) ?>]</strong>
                                        <?= htmlspecialchars($item['title']) ?>
                                        <span style="font-size: 0.8em; color: #888;"><?= date('Y.m.d', strtotime($item['created_at'])) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p>검색 결과가 없습니다.</p>
                <?php endif; ?>
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

            const searchForm = document.getElementById('searchForm');

            searchForm.addEventListener('submit', function(event) {
                event.preventDefault(); 
                const searchInput = document.getElementById('searchInput');
                const query = searchInput.value;
                if (query) {
                    const newUrl = `#!search.php/search?query=${encodeURIComponent(query)}`;
                    window.location.href = newUrl;
                }
            });
        </script>