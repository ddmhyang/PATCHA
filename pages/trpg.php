<?php
// /pages/trpg.php

$gallery_type = 'trpg';

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

<div class="gallery-container">
    <div class="gallery-header">
        <a class="trpg_title">TRPG 세션 기록</a>
        <?php if ($is_admin): ?>
        <a href="#/trpg_upload" class="upload-button">새 세션 기록</a>
        <?php endif; ?>
    </div>
    <ul class="gallery-grid">
        <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
        <li class="gallery-item">
            <a
                href="#/trpg_view?id=<?php echo $post['id']; ?>" 
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
    <?php else: ?>
        <li>게시물이 없습니다.</li>
        <?php endif; ?>
    </ul>

    <div class="pagination">
        <?php if ($total_pages > 1): ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="#/<?php echo $gallery_type; ?>?p=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php endif; ?>
    </div>
</div>

<style>

    .pagination {
        margin-top: 20px;
        text-align: center;
    }
    .pagination a,
    .pagination strong {
        display: inline-block;
        padding: 8px 12px;
        margin: 0 5px;
        border: 1px solid #ddd;
        border-radius: 5px;
        text-decoration: none;
        color: #333;
    }
    .pagination strong {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    .pagination a:hover {
        background-color: #e9e9e9;
    }

    .gallery-container {
        width: 1170px;
        padding: 20px;
        box-sizing: border-box;
        color: white;
        margin-top: 40px;
        margin-left: 40px;
    }

    .gallery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        padding-bottom: 30px;
    }

    .trpg_title {
        font-family: 'Fre9';
        font-size: 48px;
        color: rgb(255, 255, 255);
        margin: 0;
    }

    .upload-button {
        background-color: rgb(0, 0, 0);
        color: white;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        font-family: 'Fre9';
        font-size: 18px;
        transition: 0.1s ease-in-out;
    }

    .upload-button:hover {
        transform: scale(1.05);
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
        background-color: #ffffff;
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

    .admin-controls {
        position: absolute;
        top: 28px;
        right: 28px;
        z-index: 10;
        display: flex;
        gap: 5px;
    }

    .admin-controls button {
        background: white;
        border: none;
        padding: 0;
        cursor: pointer;
        width: 28px;
        height: 28px;
        font-size: 18px;
        line-height: 28px;
        text-align: center;
        border-radius: 10px;
    }
    .item-thumbnail {
        width: 250px;
        height: 250px;
        border-radius: 15px;

        background-size: cover;
        background-position: center;
        margin-bottom: 15px;
    }
</style>