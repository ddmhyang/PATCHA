<?php
// /pages/timeline.php (수정됨)
if (!isset($mysqli)) {
    // 데이터베이스 연결이 없는 경우 main.php를 통해 로드하도록 리디렉션합니다.
    $page_name = basename($_SERVER['PHP_SELF'], '.php');
    header("Location: main.php?page=" . $page_name);
    exit;
}

/**
 * HTML 콘텐츠에서 짧은 텍스트 미리보기를 추출합니다.
 */
function get_preview_from_html($html, $length = 100) {
    if (empty($html)) { return '내용 없음'; }
    $text = strip_tags($html);
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . '...';
    }
    return $text !== '' ? $text : '내용 없음';
}

// eden_gallery 테이블에서 gallery_type이 'timeline'인 게시물을 가져옵니다.
$stmt = $mysqli->prepare("SELECT id, title, content, thumbnail FROM eden_gallery WHERE gallery_type = 'timeline' ORDER BY created_at DESC");
$stmt->execute();
$timeline_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<style>
    /* 페이지의 시각적 스타일링을 정의합니다. */
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
    }
    .admin-controls {
        position: absolute;
        top: 28px;
        right: 28px;
        z-index: 10;
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
    .timeline-wrapper {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        height: 100%;
        width: 100%;
        padding: 0 95px;
        box-sizing: border-box;
        overflow-x: scroll;
        overflow-y: hidden;
    }
    .timeline-wrapper::-webkit-scrollbar {
        display: none;
    }
    .timeline-line {
        position: absolute;
        width: 100%;
        height: 1px;
        background: white;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
    }
    .timeline-events {
        display: flex;
        align-items: center;
        height: 100%;
        position: relative;
        z-index: 2;
        gap: 80px;
    }
    .timeline-item {
        display: flex;
        align-items: center;
        position: relative;
    }
    .timeline-item.top {
        margin-bottom: auto;
        margin-top: 110px;
    }
    .timeline-item.bottom {
        margin-top: auto;
        margin-bottom: 110px;
    }
    .item-container {
        display: flex;
        cursor: pointer;
        text-decoration: none;
        color: white;
    }
    .item-thumbnail {
        width: 145px;
        height: 145px;
        flex-shrink: 0;
        border-radius: 15px;
        background-color: white;
        background-size: cover;
        background-position: center;
    }
    .item-text {
        color: white;
        margin-left: 20px;
    }
    .item-text .timeline_year {
        font-size: 20px;
        font-family: 'fre9';
    }
    .item-text .timeline_title {
        font-family: 'fre1';
        font-size: 14px;
        max-width: 150px;
        white-space: normal;
        word-break: keep-all;
    }
</style>

<div id="timeline-view">
    <?php if ($is_admin): ?>
    <div class="admin-controls">
        <button id="add-btn" title="새 스토리 추가">+</button>
    </div>
    <?php endif; ?>

    <div class="timeline-line"></div>

    <div class="timeline-wrapper">
        <div class="timeline-events">
            <?php foreach ($timeline_items as $index => $item): ?>
            <div class="timeline-item <?php echo ($index % 2 == 0) ? 'top' : 'bottom'; ?>">
                <a href="#/timeline_view?id=<?php echo $item['id']; ?>" class="item-container">
                    <div
                        class="item-thumbnail"
                        style="background-image: url('<?php echo htmlspecialchars($item['thumbnail'] ?? '/img/default_thumbnail.png'); ?>');"></div>
                    <div class="item-text">
                        <div class="timeline_year">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </div>
                        <div class="timeline_title">
                            <?php echo htmlspecialchars(get_preview_from_html($item['content'])); ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // 이 스크립트는 동적으로 로드된 콘텐츠에서 이벤트가 안정적으로 작동하도록 이벤트 위임을 사용합니다. '#add-btn'이 나중에 페이지에
    // 추가되더라도 'document'에 연결된 핸들러가 클릭을 감지합니다.
    $(document)
        .off('click', '#add-btn')
        .on('click', '#add-btn', function () {
            window.location.hash = '#/timeline_upload';
        });
</script>

<script>
// 이 스크립트는 동적으로 로드된 콘텐츠에서 이벤트가 안정적으로 작동하도록 이벤트 위임을 사용합니다.
$(document).off('click', '#add-btn').on('click', '#add-btn', function () {
    // 아래 로그가 콘솔에 찍히는지 확인하기 위한 코드입니다.
    console.log("+ 버튼이 클릭되었습니다! 이제 페이지를 이동합니다."); 

    window.location.hash = '#/timeline_upload';
});
</script>