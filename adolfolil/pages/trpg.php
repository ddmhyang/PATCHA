<?php
require_once __DIR__ . '/../includes/db.php';
$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$posts = [];

$sql = "SELECT id, title, thumbnail_path, writer_name, kpc_name, pc_name, trpg_rule FROM posts WHERE type = 'trpg' ORDER BY id DESC";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $result->free();
}
?>

<div class="trpg-container">
    <div class="trpg-header">
        <h2>TRPG</h2>
        <?php if ($is_admin): ?>
            <a href="#/trpg_upload" class="add-btn">추가하기</a>
        <?php endif; ?>
    </div>

    <?php if (empty($posts)): ?>
        <p>아직 게시물이 없습니다.</p>
    <?php else: ?>
        <div class="slider-outer-container"> 
            <div class="slider-container">
                <div class="slider-wrapper">
                    <?php foreach ($posts as $post): ?>
                        <div class="slide">
                            <a href="#/trpg_view?id=<?php echo $post['id']; ?>" class="slide-link">
                                <div class="slide-content-box">
                                    <div class="thumbnail-column">
                                        <?php if (!empty($post['thumbnail_path'])): ?>
                                            <img src="/<?php echo htmlspecialchars($post['thumbnail_path']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <?php else: ?>
                                            <div class="thumbnail-placeholder"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="details-column">
                                        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                                        <ul>
                                            <li><strong>W.</strong><?php echo htmlspecialchars($post['writer_name'] ?? '정보 없음'); ?></li>
                                            <li><strong>KPC:</strong> <?php echo htmlspecialchars($post['kpc_name'] ?? '정보 없음'); ?></li>
                                            <li><strong>PC:</strong> <?php echo htmlspecialchars($post['pc_name'] ?? '정보 없음'); ?></li>
                                            <li><strong>사용 룰:</strong> <?php echo htmlspecialchars($post['trpg_rule'] ?? '정보 없음'); ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="slider-btn prev">&#10094;</button>
            <button class="slider-btn next">&#10095;</button>
        </div>
        <div class="dots-container"></div>
    <?php endif; ?>
</div>

<style>
    .slider-outer-container {
        position: relative;
    }

    .slider-container {
        position: relative;
        width: 750px;
        margin: auto;
        overflow: hidden;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .slider-wrapper {
        display: flex;
        transition: transform 0.5s ease-in-out;
    }

    .slide {
        min-width: 100%;
        box-sizing: border-box;
    }
    
    .slide-link {
        display: block;
        text-decoration: none;
        color: inherit;
    }
    .slide-link:hover {
        transform: none !important;
    }

    .slide-content-box {
        display: flex;
        background-color: #222;
        border-radius: 8px;
        overflow: hidden;
        margin: 0 10px;
        border: 1px solid #444;
    }

    .thumbnail-column {
        width: 400px;
        height: 300px;
        flex-shrink: 0;
    }
    .thumbnail-column img, .thumbnail-placeholder {
        width: 100%;
        height: 100%;
        object-fit: cover; 
    }
    .thumbnail-placeholder {
        background-color: #1a1a1a;
    }

    .details-column {
        flex-grow: 1;
        padding: 30px;
        color: #fafafa;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .details-column h2 {
        margin-top: 0;
        margin-bottom: 30px;
        font-family: 'DungGeunMo', sans-serif;
        font-size: 20px;
        border-bottom: 2px solid #555;
        padding-bottom: 15px;
    }
    .details-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .details-column li {
        font-family: 'Galmuri9', sans-serif;
        font-size: 16px;
        margin-bottom: 18px;
    }
    .details-column li strong {
        font-weight: normal;
        margin-right: 10px;
        color: #999;
    }
    .details-column li:last-child {
        margin-bottom: 0;
    }

    .slider-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(250, 250, 250, 0);
        border: none;
        color: #1a1a1a;
        font-size: 24px;
        cursor: pointer;
        z-index: 10;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .slider-btn.prev { left: calc(50% - 425px - 20px); }
    .slider-btn.next { right: calc(50% - 425px - 20px); }

    .dots-container {
        text-align: center;
        padding: 20px 0;
    }
    .dot {
        height: 12px;
        width: 12px;
        margin: 0 5px;
        background-color: #555;
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .dot.active, .dot:hover { background-color: #fafafa; }

    @media (max-width: 768px) {
        .slider-container{
            margin-top: 30px;
            width: 400px;
        }

        
        .slide-content-box {
            flex-direction: column;
        }

        
        .slider-btn.prev {
            left: 0px;
        }
        .slider-btn.next { 
            right: 0px; 
        }
        
    }
</style>

<script>
$(document).ready(function() {
    const sliderContainer = $('.slider-container');
    const sliderWrapper = $('.slider-wrapper');
    const slides = $('.slide');
    const slideCount = slides.length;

    if (slideCount === 0) return;

    const dotsContainer = $('.dots-container');
    const prevBtn = $('.prev');
    const nextBtn = $('.next');
    
    let currentIndex = 0;
    let isDragging = false;
    let startPos = 0;
    let currentTranslate = 0;
    let prevTranslate = 0;

    for (let i = 0; i < slideCount; i++) {
        dotsContainer.append(`<span class="dot" data-index="${i}"></span>`);
    }
    const dots = $('.dot');

    function goToSlide(index) {
        if (index < 0) {
            index = slideCount - 1;
        } else if (index >= slideCount) {
            index = 0;
        }

        currentTranslate = index * -slides.first().width();
        prevTranslate = currentTranslate;
        sliderWrapper.css('transform', `translateX(${currentTranslate}px)`);

        currentIndex = index;
        updateDots();
    }

    function updateDots() {
        dots.removeClass('active');
        dots.eq(currentIndex).addClass('active');
    }

    nextBtn.on('click', () => goToSlide(currentIndex + 1));
    prevBtn.on('click', () => goToSlide(currentIndex - 1));

    dotsContainer.on('click', '.dot', function() {
        goToSlide($(this).data('index'));
    });

    sliderContainer.on('mousedown touchstart', function(e) {
        isDragging = true;
        startPos = getPositionX(e);
        sliderWrapper.css('transition', 'none');
    });

    sliderContainer.on('mouseup touchend', function(e) {
        if (!isDragging) return;
        isDragging = false;
        const endPos = getPositionX(e);
        const movedBy = currentTranslate - prevTranslate;

        sliderWrapper.css('transition', 'transform 0.5s ease-in-out');

        if (movedBy < -50) {
             goToSlide(currentIndex + 1);
        } else if (movedBy > 50) {
            goToSlide(currentIndex - 1);
        } else {
             goToSlide(currentIndex);
        }
    });

    sliderContainer.on('mouseleave', function() {
        if(isDragging) {
            isDragging = false;
            sliderWrapper.css('transition', 'transform 0.5s ease-in-out');
            goToSlide(currentIndex);
        }
    });

    sliderContainer.on('mousemove touchmove', function(e) {
        if (isDragging) {
            const currentPosition = getPositionX(e);
            currentTranslate = prevTranslate + currentPosition - startPos;
            sliderWrapper.css('transform', `translateX(${currentTranslate}px)`);
        }
    });

    sliderWrapper.on('click', '.slide-link', function(e) {
        if (Math.abs(currentTranslate - prevTranslate) > 5) {
            e.preventDefault();
        }
    });

    function getPositionX(e) {
        return e.type.includes('mouse') ? e.pageX : e.originalEvent.touches[0].clientX;
    }
    
    $(window).on('resize', function() {
        goToSlide(currentIndex);
    });

    goToSlide(0);
});
</script>