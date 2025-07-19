<?php

$current_gallery_type = isset($_GET['type']) ? $_GET['type'] : 'gallery1';

$allowed_gallery_types = ['gallery1', 'gallery2', 'gallery1'];
if (!in_array($current_gallery_type, $allowed_gallery_types)) {
    $current_gallery_type = 'gallery1';
}

$gallery_type = $current_gallery_type; 
$posts_per_page = 9;
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

$total_count_stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM eden_gallery WHERE gallery_type = ?");
$total_count_stmt->bind_param("s", $gallery_type);
$total_count_stmt->execute();
$total_posts = $total_count_stmt->get_result()->fetch_assoc()['total'];
$total_count_stmt->close();

$total_pages = ceil($total_posts / $posts_per_page);
$offset = ($current_page - 1) * $posts_per_page;

$stmt = $mysqli->prepare("SELECT id, title, created_at, content, thumbnail FROM eden_gallery WHERE gallery_type = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("sii", $gallery_type, $posts_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();
?>

<div class="gallery-page-wrapper">
    <div class="left-menu">
        <a
            href="#/gallery1?type=gallery1"
            class="<?php echo ($current_gallery_type === 'gallery1') ? 'active' : ''; ?>">Main</a>
        <a
            href="#/gallery2?type=gallery2"
            class="<?php echo ($current_gallery_type === 'gallery2') ? 'active' : ''; ?>">Add</a>
        <a
            href="#/gallery_etc?type=gallery_etc"
            class="<?php echo ($current_gallery_type === 'gallery_etc') ? 'active' : ''; ?>">Etc</a>
    </div>

    <div class="gallery-content-area">
        <h1 class="gallery-main-title">
        <?php
            if ($current_gallery_type === 'gallery1') echo '메인 갤러리';
            else if ($current_gallery_type === 'gallery2') echo '추가 갤러리';
            else if ($current_gallery_type === 'gallery_etc') echo '기타 갤러리';
        ?>
        </h1>

        <?php if ($is_admin): ?>
        <div class="admin-controls">
            <a
                href="#/gallery_upload?type=<?php echo htmlspecialchars($current_gallery_type); ?>"
                class="btn-action">새 게시물 추가</a>
        </div>
        <?php endif; ?>
        <hr>
        <ul class="gallery-grid">
            <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
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

                $thumbnail_style = !empty($display_thumbnail) ? 'background-image: url(\'' . htmlspecialchars($display_thumbnail) . '\');' : 'background-color: white;';
                ?>
                    <div class="item-thumbnail" style="<?php echo $thumbnail_style; ?>"></div>
                    <div class="item-text">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <span class="post-date"><?php echo date('Y.m.d', strtotime($post['created_at'])); ?></span>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-posts">아직 게시물이 없습니다.</p>
            <?php endif; ?>
        </ul>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
            <a
                href="#/<?php echo $gallery_type; ?>?type=<?php echo htmlspecialchars($current_gallery_type); ?>&p=<?php echo $current_page - 1; ?>"
                class="page-link">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a
                href="#/<?php echo $gallery_type; ?>?type=<?php echo htmlspecialchars($current_gallery_type); ?>&p=<?php echo $i; ?>"
                class="page-link <?php echo ($i === $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
            <a
                href="#/<?php echo $gallery_type; ?>?type=<?php echo htmlspecialchars($current_gallery_type); ?>&p=<?php echo $current_page + 1; ?>"
                class="page-link">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
   
    .gallery-page-wrapper {
        display: flex;
        gap: 32px;
        position: absolute;
        top: 220px;
        left: 96px;
        width: 1250px;
        height: 605px;
    }
    .left-menu {
        width: 204px;
        height: 100%;
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        display: flex;
        flex-direction: column;
        gap: 140px;
        padding-top: 107px;
        box-sizing: border-box;
    }
    .left-menu > a {
        color: #FFF;
        text-align: center;
        font-family: 'Fre1';
        font-size: 32px;
        cursor: pointer;
        text-decoration-line: none;
    }
    .left-menu > a.active {
        font-family: 'Fre9';
    }
    .gallery-content-area {
        width: 1016px;
        height: 100%;
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 20px 20px 20px 30px;
        box-sizing: border-box;
        color: white;
        overflow-y: scroll;
    }
    .gallery-content-area::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
    .gallery-main-title {
        text-align: center;
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 44px;
        margin-top: 40px;
    }
    .admin-controls {
        text-align: right;
        margin-bottom: 20px;
    }
    .admin-controls .btn-action {
        margin-top: -20px;
        display: inline-block;
        background-color: rgb(255, 255, 255);
        color: black;
        margin-right: 28px;
        margin-bottom: 28px;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-family: 'Fre9';
        font-size: 18px;
        transition: 0.1s ease-in-out;
    }
    .gallery-content-area hr {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.3);
        margin: 20px 0;
        transform: scale(0);
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
        width: 250px;
        text-align: center;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .item-link {
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .item-thumbnail {
        width: 200px;
        height: 200px;
        border-radius: 15px;
        background-color: #ffffff;
        background-size: cover;
        background-position: center;
        margin-bottom: 15px;
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
    .no-posts {
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
        font-family: 'Fre3';
        font-size: 18px;
        grid-column: 1 / -1;
    }
    .pagination {
        margin-top: 30px;
        text-align: center;
        padding: 10px 0;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 8px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .pagination .page-link {
        display: inline-block;
        padding: 10px 15px;
        margin: 0 5px;
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
        font-family: 'Fre1', sans-serif;
        font-size: 16px;
    }
    .pagination .page-link:hover {
        background-color: rgb(255, 0, 0);
        color: #ffd700;
    }
    .pagination .page-link.active {
        background-color: #ffd700;
        color: black;
        font-weight: bold;
    }

   
    @media (max-width: 768px) {
        .gallery-page-wrapper {
            position: static;
            display: block;
            height: auto;
        }

        .gallery-content-area {
            position: absolute !important;
            top: 273px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 804px;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
            padding: 60px 40px;
            box-sizing: border-box;
            margin: 0;
            overflow-y: scroll;
        }

        .left-menu {
            display: flex;
            padding-top: 0px;
            width: 100%;
            height: 150px;
            flex-shrink: 0;
            background: linear-gradient(180deg, rgb(0, 0, 0) 0%, rgba(0, 0, 0) 100%);
            box-sizing: border-box;
            flex-direction: row;
            position: absolute;
            bottom: 0px;
            gap: 108px;
            left: 0px;
        }
        .left-menu > a {
            color: #FFF;
            text-align: center;
            font-family: 'Fre1';
            font-size: 32px;
            font-style: normal;
            line-height: normal;
            cursor: pointer;
            text-decoration-line: none;
            display: block;
            margin-left: 83px;
            margin-top: 60px;
        }

        .gallery-main-title {
            font-size: 40px;
            margin-top: 0;
            text-align: left;
        }

       
        .admin-controls {
           
            position: absolute;
            top: 60px;
           
            right: 40px;
           
            margin: 0;
        }
        .admin-controls .btn-action {
           
            background-color: rgb(255, 255, 255);
            color: black;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-family: 'Fre9';
            font-size: 18px;
            transition: 0.1s ease-in-out;
            margin: 0;
           
        }

        .gallery-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
        }

        .gallery-item {
            width: 235px;
            flex-direction: row;
           
        }
        .item-thumbnail {
            width: 180px;
            height: 180px;
            margin-top: 15px;
           
            margin-bottom: 15px;
        }
        .gallery-content-area hr {
            border: none;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            margin: 30px 0 30px 0;
            transform: scale(1);
        }
       
    }
</style>