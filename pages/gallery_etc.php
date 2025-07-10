<?php
// /pages/gallery_etc.php
$current_gallery_type = isset($_GET['type']) ? $_GET['type'] : 'gallery_etc';

$allowed_gallery_types = ['gallery1', 'gallery2', 'gallery_etc'];
if (!in_array($current_gallery_type, $allowed_gallery_types)) {
    $current_gallery_type = 'gallery_etc';
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
            href="main.php?page=gallery1&type=gallery1"
            class="<?php echo ($current_gallery_type === 'gallery_1') ? 'active' : ''; ?>">Main</a>
        <a
            href="main.php?page=gallery_etc&type=gallery2"
            class="<?php echo ($current_gallery_type === 'gallery2') ? 'active' : ''; ?>">Add</a>
        <a
            href="main.php?page=gallery_etc&type=gallery_etc"
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
                href="main.php?page=gallery_upload&type=<?php echo htmlspecialchars($current_gallery_type); ?>"
                class="btn-action">새 게시물 추가</a>
        </div>
        <?php endif; ?>

        <ul class="gallery-grid">
            <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
            <li class="gallery-item">
                <a
                    href="main.php?page=gallery_view&id=<?php echo $post['id']; ?>"
                    class="item-link">
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
                                $thumbnail_style = 'background-color: white;';
                                $thumbnail_content = '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: black; font-size: 14px;">No Image</div>';
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
        <?php else: ?>
            <p class="no-posts">아직 게시물이 없습니다.</p>
            <?php endif; ?>
        </ul>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($current_page > 1): ?>
            <a
                href="main.php?page=gallery_etc&type=<?php echo htmlspecialchars($current_gallery_type); ?>&p=<?php echo $current_page - 1; ?>"
                class="page-link">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a
                href="main.php?page=gallery_etc&type=<?php echo htmlspecialchars($current_gallery_type); ?>&p=<?php echo $i; ?>"
                class="page-link <?php echo ($i === $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
            <a
                href="main.php?page=gallery_etc&type=<?php echo htmlspecialchars($current_gallery_type); ?>&p=<?php echo $current_page + 1; ?>"
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
        font-style: normal;
        line-height: normal;
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
        background-color: rgb(0, 0, 0);
        color: white;
        margin-right: 28px;
        margin-bottom: 28px;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-family: 'Fre9';
        font-size: 18px;
        transition: 0.1s ease-in-out;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 17px;
        list-style: none;
        padding: 0;
        margin: 0 0 50px;
    }

    .gallery-item {
        background-color: rgba(0, 0, 0, 0.7);
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        padding: 25px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin: 0 29px;
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
        border-radius: 10px;
        background-color: white;
        background-size: cover;
        background-position: center;
        margin-bottom: 15px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
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
</style>