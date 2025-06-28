<?php
// /pages/gallery1.php
$gallery_type = 'gallery1';

// --- 페이지네이션 로직 시작 ---

// 1. 한 페이지에 보여줄 게시물 수
$posts_per_page = 10;

// 2. 현재 페이지 번호 가져오기 (URL에 ?p=N 와 같은 형태로 전달)
$current_page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($current_page < 1) {
    $current_page = 1;
}

// 3. 전체 게시물 수 계산
$total_count_stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM eden_gallery WHERE gallery_type = ?");
$total_count_stmt->bind_param("s", $gallery_type);
$total_count_stmt->execute();
$total_posts = $total_count_stmt->get_result()->fetch_assoc()['total'];
$total_count_stmt->close();

// 4. 전체 페이지 수 계산
$total_pages = ceil($total_posts / $posts_per_page);

// 5. 현재 페이지에 대한 OFFSET 계산
$offset = ($current_page - 1) * $posts_per_page;

// --- 페이지네이션 로직 끝 ---


// 6. 현재 페이지에 해당하는 게시물 목록만 불러오기 (LIMIT, OFFSET 추가)
$stmt = $mysqli->prepare("SELECT id, title, created_at FROM eden_gallery WHERE gallery_type = ? ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("sii", $gallery_type, $posts_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<div class="gallery-container">
    <h1>Gallery 1</h1>
    <?php if ($is_admin): ?>
        <a href="main.php?page=gallery_upload&type=<?php echo $gallery_type; ?>" class="btn-upload">새 글 작성</a>
    <?php endif; ?>

    <ul class="post-list">
        <?php if ($total_posts > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                    <a href="main.php?page=gallery_view&id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a>
                    <span class="post-date"><?php echo $row['created_at']; ?></span>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>게시물이 없습니다.</li>
        <?php endif; ?>
    </ul>

    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php if ($current_page > 1): ?>
                <a href="main.php?page=<?php echo $gallery_type; ?>&p=<?php echo $current_page - 1; ?>">&laquo; 이전</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <strong class="current-page"><?php echo $i; ?></strong>
                <?php else: ?>
                    <a href="main.php?page=<?php echo $gallery_type; ?>&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="main.php?page=<?php echo $gallery_type; ?>&p=<?php echo $current_page + 1; ?>">다음 &raquo;</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
/* 간단한 페이지네이션 CSS 예시 */
.pagination { margin-top: 20px; text-align: center; }
.pagination a, .pagination strong { display: inline-block; padding: 5px 10px; margin: 0 2px; border: 1px solid #ddd; text-decoration: none; color: #333; }
.pagination a:hover { background-color: #f0f0f0; }
.pagination .current-page { font-weight: bold; color: #fff; background-color: #555; border-color: #555; }
</style>