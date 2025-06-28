<?php
// /pages/search.php

// 1. 검색어 가져오기
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$search_results = [];

if (!empty($query)) {
    // 2. SQL LIKE 구문에 사용할 검색어 패턴 생성
    $search_term = "%" . $query . "%";
    
    // 3. 제목 또는 내용에서 검색
    $stmt = $mysqli->prepare("SELECT id, title, content, created_at, gallery_type FROM eden_gallery WHERE title LIKE ? OR content LIKE ? ORDER BY id DESC");
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
        <p>총 <?php echo count($search_results); ?>개의 게시물을 찾았습니다.</p>
        <ul class="post-list">
            <?php foreach ($search_results as $post): ?>
                <li>
                    <a href="main.php?page=gallery_view&id=<?php echo $post['id']; ?>">
                        <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                        <p><?php echo mb_substr(strip_tags($post['content']), 0, 150, 'utf-8'); ?>...</p> </a>
                    <span class="post-meta">
                        <?php echo $post['created_at']; ?> | 
                        in <a href="main.php?page=<?php echo htmlspecialchars($post['gallery_type']); ?>"><?php echo htmlspecialchars($post['gallery_type']); ?></a>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>검색 결과가 없습니다.</p>
    <?php endif; ?>
</div>
<style>
.post-list .post-meta { font-size: 0.8em; color: #777; }
.post-list p { margin: 5px 0 0; color: #555; }
</style>