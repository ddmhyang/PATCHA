<?php
require_once '../includes/db.php';

$gallery_type = 'art'; 

$posts = $mysqli->query("SELECT id, title, thumbnail, is_private FROM gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-container" id="main_content">
    <div class="main-frame">
        <div class="deco-tape tape-1">Hello</div>
        <div class="deco-tape tape-2">World !</div>

        <div class="left-section">
            <i class="fa-solid fa-book-open floating-icon fi-1"></i>
            <i class="fa-solid fa-pen-nib floating-icon fi-2"
                style="left: 160px; bottom: 50px; transform: rotate(45deg);"></i>

            <div class="sub-title">Category</div>
            <h1><?php echo ucfirst($gallery_type); ?></h1>

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

        <div class="right-section-content">
            <div class="gallery-grid">
                <?php if (count($posts) > 0): ?>
                    <?php foreach ($posts as $post): ?>
                        <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="gallery-item">
                            
                            <?php
                                $thumbnail_url = $post['thumbnail'] ?? '';
                                // 이미지가 있으면 url 설정, 없으면 기본색상
                                $style = !empty($thumbnail_url) 
                                    ? "background-image: url('" . htmlspecialchars($thumbnail_url) . "');" 
                                    : "background-color: #f0f0f0;"; // 이미지가 없을 때 색상
                            ?>
                            
                            <div class="img-placeholder" style="<?php echo $style; ?>">
                                <?php if (empty($thumbnail_url)): ?>
                                    <i class="fa-regular fa-image" style="color: #ccc;"></i>
                                <?php endif; ?>
                            </div>

                            <div class="gallery-info">
                                <h3 class="gallery-title"><?php echo htmlspecialchars($post['title']);?></h3>
                                <?php if($post['is_private']): ?>
                                    <span style="font-size: 12px; color: red;">🔒 비공개</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="padding: 20px; color: #666;">등록된 게시글이 없습니다.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>