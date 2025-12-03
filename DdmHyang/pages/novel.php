<?php
require_once '../includes/db.php';

$gallery_type = 'novel';

$posts = $mysqli->query("SELECT id, title, content, is_private, tags FROM gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

function get_summary($content) {
    $text = strip_tags($content); 
    if (mb_strlen($text) > 50) {
        return mb_substr($text, 0, 50) . '...';
    }
    return $text;
}
?>

<div class="page-container" id="main_content">
    <div class="main-frame">
        <div class="left-section" id="novel-left">
            <i class="fa-solid fa-book-open floating-icon fi-1"></i>
            <i class="fa-solid fa-feather-pointed floating-icon fi-2"
                style="left: 180px; bottom: 40px;"></i>

            <div class="sub-title">Category</div>
            <h1>Novel</h1>
            <p class="description">
                여긴 또 뭘 쓰지
            </p>

            <?php if ($is_admin): ?>
                <a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="action-btn write-btn">
                    <i class="fa-solid fa-pen"></i> 새 글 작성
                </a>
            <?php endif; ?>

            <a href="#/" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                메인으로 돌아가기
            </a>
        </div>

        <div class="right-section-content" id="novel-right">
            <ul class="novel-list">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <li class="novel-item" onclick="location.href='#/gallery_view?id=<?php echo $post['id']; ?>'">
                            <h3 class="novel-title">
                                <?php echo htmlspecialchars($post['title']); ?>
                                <?php if($post['is_private']): ?>
                                    <span style="font-size: 0.8rem; color: #ff6b6b; margin-left:5px;">🔒</span>
                                <?php endif; ?>
                            </h3>
                            
                            <p class="novel-desc">
                                <?php echo htmlspecialchars(get_summary($post['content'])); ?>
                            </p>
                            
                            <div class="novel-tags">
                                <?php 
                                if (!empty($post['tags'])) {
                                    $tag_list = explode(',', $post['tags']);
                                    
                                    foreach ($tag_list as $tag) {
                                        $tag = trim($tag); 
                                        if (!empty($tag)) {
                                            echo '<span class="tag">#' . htmlspecialchars($tag) . '</span>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="novel-item" style="cursor: default; text-align: center;">
                        <p class="novel-desc">등록된 글이 없습니다.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>