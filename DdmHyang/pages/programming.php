<?php
require_once '../includes/db.php';

// 'programming' 타입의 게시글만 가져오기
$gallery_type = 'programming';

// 태그(tags)도 함께 가져오도록 쿼리 작성
$posts = $mysqli->query("SELECT id, title, content, is_private, tags FROM gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// HTML 태그 제거 및 내용 요약 함수 (한 줄 소개용)
function get_summary($content) {
    $text = strip_tags($content); // HTML 태그 제거
    if (mb_strlen($text) > 60) {
        return mb_substr($text, 0, 60) . '...';
    }
    return $text;
}
?>

<div class="page-container" id="main_content">
    <div class="main-frame">
        <div class="deco-tape tape-1">Hello</div>
        <div class="deco-tape tape-2">World !</div>

        <div class="left-section" id="programming-left">
            <i class="fa-solid fa-code floating-icon fi-1"></i>
            <i class="fa-solid fa-gear floating-icon fi-2"
                style="left: 170px; bottom: 60px;"></i>

            <div class="sub-title">Category</div>
            <h1>Programing</h1>
            <p class="description">
                따~~악!!!!<br>
                <b>버그 하나만 더 고치고</b><br>
                잔다 내가!!!!
            </p>


            <?php if ($is_admin): ?>
                <a href="#/gallery_upload?type=<?php echo $gallery_type; ?>" class="action-btn write-btn">
                    <i class="fa-solid fa-pen"></i> 새 글 작성</a>
            <?php endif; ?>

            <a href="#/" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
                메인으로 돌아가기
            </a>
        </div>

        <div class="right-section-content" id="programming-right">
            <ul class="prog-list">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <li class="prog-item" onclick="location.href='#/gallery_view?id=<?php echo $post['id']; ?>'">
                            <h3 class="prog-title">
                                <i class="fa-brands fa-unity"></i>
                                <?php echo htmlspecialchars($post['title']); ?>
                                
                                <?php if($post['is_private']): ?>
                                    <span style="font-size: 0.8rem; color: #ff6b6b; margin-left:5px;">🔒</span>
                                <?php endif; ?>
                            </h3>
                            
                            <p class="prog-desc">
                                <?php echo htmlspecialchars(get_summary($post['content'])); ?>
                            </p>
                            
                            <div class="tech-stack">
                                <?php 
                                if (!empty($post['tags'])) {
                                    $tag_list = explode(',', $post['tags']);
                                    foreach ($tag_list as $tag) {
                                        $tag = trim($tag);
                                        if (!empty($tag)) {
                                            echo '<span class="tech-badge">' . htmlspecialchars($tag) . '</span>';
                                        }
                                    }
                                } else {
                                    echo '<span class="tech-badge" style="background:#eee; color:#aaa;">Etc</span>';
                                }
                                ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="prog-item" style="cursor: default; text-align: center;">
                        <p class="prog-desc">등록된 프로젝트가 없습니다.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>