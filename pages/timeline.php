<?php
// /pages/timeline.php
if (!isset($mysqli)) {
    $page_name = basename($_SERVER['PHP_SELF'], '.php');
    header("Location: main.php?page=" . $page_name);
    exit;
}


function get_title_from_html($html) {
    if (empty($html)) { return '제목 없음'; }
    $text = strip_tags($html);
    $lines = preg_split('/\\r\\n|\\r|\\n/', $text);
    foreach ($lines as $line) {
        if (trim($line) !== '') {
            return trim($line);
        }
    }
    return '제목 없음';
}


$stmt = $mysqli->prepare("SELECT id, year, full_description, thumbnail FROM eden_timeline ORDER BY sort_order ASC");
$stmt->execute();
$timeline_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$total_width = (count($timeline_items) * 259) + 200; 
?>

<style>

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
        justify-content: space-around;
        height: 100%;
        position: relative;
        z-index: 2;
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
        margin-right: 50px;
    }
    .item-text .timeline_year {
        font-size: 20px;
        font-family: 'fre9';
    }
    .item-text .timeline_title {
        font-family: 'fre1';
        font-size: 14px;
        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 auto;
    }

    .timeline-item.ui-sortable-helper {
        cursor: grabbing;
        opacity: 0.7;
    }

    #add-view {
        padding: 20px 30px;
        height: 100%;
        box-sizing: border-box;
        overflow-y: auto;
        color: white;
    }

    #add-view form input[type="text"],
    #add-view form textarea {
        width: 100%;
        margin-bottom: 15px;
        padding: 8px;
        box-sizing: border-box;
        background: #333;
        color: white;
        border: 1px solid #555;
    }

    #add-view form label {
        display: block;
        margin-bottom: 5px;
        font-family: 'fre9';
    }
</style>

<div id="timeline-view">
    <?php if ($is_admin): ?>
    <div class="admin-controls">
        <button id="add-btn">+</button>
        <button
            id="save-order-btn"
            style="display:none; font-size: 16px; width:auto; height:auto;">순서 저장</button>
    </div>
    <?php endif; ?>

    <div class="timeline-line"></div>

    <div class="timeline-wrapper">
        <div class="timeline-events" id="timeline-sortable">
            <?php foreach ($timeline_items as $index => $item): ?>
            <div
                class="timeline-item <?php echo ($index % 2 == 0) ? 'top' : 'bottom'; ?>"
                data-id="<?php echo $item['id']; ?>">
                <div class="item-container" data-id="<?php echo $item['id']; ?>">
                    <div
                        class="item-thumbnail"
                        style="background-image: url('<?php echo htmlspecialchars($item['thumbnail'] ?? ''); ?>');"></div>
                    <div class="item-text">
                        <div class="timeline_year">
                            <?php echo htmlspecialchars($item['year']); ?>
                        </div>
                        <div class="timeline_title">
                            <?php echo htmlspecialchars(get_title_from_html($item['full_description'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="detail-view" style="display: none;"></div>

<?php if ($is_admin): ?>
<div id="add-view" style="display: none;">
    <h1 style="color:white;">새 스토리 추가</h1>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <form id="add-timeline-form" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_full_item">
        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo $_SESSION['csrf_token']; ?>">

        <label>제목:</label>
        <input type="text" name="year" required="required">

        <label>썸네일 이미지:</label>
        <input
            type="file"
            name="thumbnail_file"
            accept="image/*"
            style="background:transparent; border:none;">

        <label style="margin-top:15px;">상세 설명:</label>
        <textarea id="add-summernote" name="full_description"></textarea>

        <div style="margin-top:20px;">
            <button
                type="submit"
                style="font-family:Fre7; background:rgb(255, 255, 255); color:black; border:1px; borde-radius 10px; padding: 5px 10px; cursor:pointer; margin-top:5px;">추가하기</button>
            <button
                type="button"
                id="add-cancel-btn"
                style="font-family:Fre7; background:rgb(0, 0, 0); color:white; border:none; padding: 5px 10px; cursor:pointer; margin-top:5px;">취소</button>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
$(document).ready(function () {
    // 타임라인 아이템 클릭 시, main.php의 라우터가 처리하도록 링크로 만듭니다.
    // 기존의 복잡한 show/hide 로직 대신, 페이지 이동으로 처리합니다.
    $('.item-container').each(function() {
        const itemId = $(this).data('id');
        // ajax_timeline_handler.php를 더 이상 사용하지 않고,
        // #/timeline_detail?id=... 과 같은 SPA 경로로 링크를 겁니다. (timeline_detail.php는 새로 만들어야 합니다)
        // 우선, 클릭 이벤트를 제거하여 main.php가 처리하도록 합니다.
        // 이 부분은 추가 개발이 필요해 보입니다. 지금은 리로드 문제부터 해결합니다.
    });

    // 순서 변경 UI 초기화는 남겨둡니다.
    $("#timeline-sortable").sortable({
        axis: "x",
        placeholder: "ui-state-highlight",
        update: function (event, ui) {
            $("#save-order-btn").fadeIn();
        }
    }).disableSelection();

    // 순서 저장 버튼은 main.php의 중앙 핸들러가 처리하도록 action을 가진 form으로 감싸거나,
    // 이 스크립트를 수정해야 합니다. 우선 리로드 문제부터 해결하기 위해 그대로 둡니다.
});
</script>