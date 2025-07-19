<?php
// /pages/search.php

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$search_results = [];

if (!empty($query)) {
    $search_term = "%" . $query . "%";
    $stmt = $mysqli->prepare("SELECT id, title, content, created_at, gallery_type, thumbnail FROM eden_gallery WHERE title LIKE ? OR content LIKE ? ORDER BY id DESC");
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    $stmt->close();
}
?>

<div class="search-results-container">
    <h1>'<?php echo htmlspecialchars($query); ?>'에 대한 검색 결과</h1>

    <?php if (empty($query)): ?>
    <p>검색어를 입력해주세요.</p>
<?php elseif (!empty($search_results)): ?>
    <p class="search_result">총
        <?php echo count($search_results); ?>개의 게시물을 찾았습니다.</p>
    <ul class="gallery-grid">
        <?php foreach ($search_results as $post): ?>
        <li class="gallery-item">
            <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="item-link">
            <?php
                        $display_thumbnail = $post['thumbnail'];
                        if (empty($display_thumbnail)) {
                            preg_match('/<img[^>]+src="([^">]+)"/', $post['content'], $matches);
                            if (isset($matches[1])) {
                                $display_thumbnail = $matches[1];
                            }
                        }

                        $thumbnail_style = '';
                        $thumbnail_content = '';
                        if (!empty($display_thumbnail)) {
                            $thumbnail_style = 'background-image: url(\'' . htmlspecialchars($display_thumbnail) . '\');';
                            $thumbnail_content = '';
                        } else {
                            $thumbnail_style = 'background-color: #ffffff;';
                            $thumbnail_content = '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: black; font-size: 14px;"></div>';
                        }
                        ?>
                <div class="item-thumbnail" style="<?php echo $thumbnail_style; ?>">
                    <?php echo $thumbnail_content; ?>
                </div>
                <div class="item-text">
                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                    <span class="post-date"><?php echo date('Y.m.d', strtotime($post['created_at'])); ?></span>
                </div>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p class="search_result">검색 결과가 없습니다.</p>
    <?php endif; ?>
</div>

<style>
    .content {
        position: absolute !important;
        top: 220px;
        left: 50%;
        transform: translateX(-50%);
        width: 1250px;
        height: 605px;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 0;
        box-sizing: border-box;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .content::-webkit-scrollbar {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background-color: #555;
        border-radius: 4px;
    }
    .content::-webkit-scrollbar-track {
        background-color: #333;
    }
    .search-results-container {
        width: 1170px;
        padding: 20px;
        box-sizing: border-box;
        color: white;
        margin-left: 40px;
        margin-top: 20px;
    }
    .search-results-container h1 {
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 40px;
        margin-bottom: 30px;
    }
    .search-results-container > p {
        color: rgb(255, 255, 255);
        font-family: 'Fre3';
        font-size: 24px;
        margin-bottom: 30px;
    }
    .search_result {
        color: rgb(255, 255, 255);
        font-family: 'Fre3';
        font-size: 20px;
        margin-bottom: 30px;
    }
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        list-style: none;
        padding: 0;
        margin: 0;
        justify-items: center;
        margin-bottom: 30px;
    }
    .gallery-item {
        background-color: rgba(0, 0, 0, 0.6);
        padding: 15px;
        border-radius: 10px;
        width: 300px;
        text-align: center;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .gallery-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
    }
    .item-link {
        text-decoration: none;
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .item-thumbnail {
        width: 250px;
        height: 250px;
        border-radius: 15px;
        background-size: cover;
        background-position: center;
        margin-bottom: 15px;
    }
    .item-text {
        width: 100%;
    }
    .item-text h3 {
        font-family: 'Fre1', sans-serif;
        font-size: 22px;
        color: #F8F8F8;
        margin: 0 0 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .item-text .post-date {
        font-family: 'Fre1', sans-serif;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.6);
    }

    @media (max-width: 768px) {
        .content {
            position: absolute !important;
            top: 273px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 900px;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
            padding: 0;
            box-sizing: border-box;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .content::-webkit-scrollbar {
            width: 8px;
        }
        .content::-webkit-scrollbar-thumb {
            background-color: #555;
            border-radius: 4px;
        }
        .content::-webkit-scrollbar-track {
            background-color: #333;
        }
        .search-results-container {
            width: 600px;
            padding: 20px;
            box-sizing: border-box;
            color: white;
            margin-left: 0px;
            margin-top: 20px;
        }
        .search-results-container h1 {
            color: rgb(255, 255, 255);
            font-family: 'Fre7';
            font-size: 40px;
            margin-left: 40px;
            margin-bottom: 30px;
        }
        .search-results-container > p {
            color: rgb(255, 255, 255);
            font-family: 'Fre3';
            margin-left: 40px;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .search_result {
            color: rgb(255, 255, 255);
            font-family: 'Fre3';
            font-size: 20px;
            margin-left: 40px;
            margin-bottom: 30px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 50px;
            list-style: none;
            padding: 0;
            margin: 0;
            justify-items: center;
            margin-bottom: 30px;
        }
        .gallery-item {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 15px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }
        .item-link {
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        .item-thumbnail {
            width: 250px;
            height: 250px;
            border-radius: 15px;
            background-size: cover;
            background-position: center;
            margin-bottom: 15px;
        }
        .item-text {
            width: 100%;
        }
        .item-text h3 {
            font-family: 'Fre1', sans-serif;
            font-size: 22px;
            color: #F8F8F8;
            margin: 0 0 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-text .post-date {
            font-family: 'Fre1', sans-serif;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
    }
</style>