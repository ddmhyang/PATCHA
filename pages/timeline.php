<?php
// /pages/timeline.php (수정)
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
?>

<style>
    /* 기존 스타일 코드는 그대로 유지합니다. */
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

    .admin-controls button, .admin-controls a {
        background: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 16px;
        border-radius: 10px;
        text-decoration: none;
        color: black;
        font-family: 'Fre7';
        line-height: 1.5;
    }
    .admin-controls a {
        padding: 7px 10px;
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

    .timeline-wrapper::-webkit-scrollbar { display: none; }

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
        justify-content: flex-start;
        gap: 114px;
        height: 100%;
        position: relative;
        z-index: 2;
        padding: 0 50px;
    }

    .timeline-item {
        display: flex;
        align-items: center;
        position: relative;
        flex-shrink: 0;
    }

    .timeline-item.top { margin-bottom: auto; margin-top: 110px; }
    .timeline-item.bottom { margin-top: auto; margin-bottom: 110px; }

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

    .item-text { color: white; margin-left: 20px; }
    .item-text .timeline_year { font-size: 20px; font-family: 'fre9'; }
    .item-text .timeline_title {
        font-family: 'fre1';
        font-size: 14px;
        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 auto;
    }
</style>

<div id="timeline-view">
    <?php if ($is_admin): ?>
    <div class="admin-controls">
        <a href="#/timeline_add" class="btn-add">+</a>
        <button id="save-order-btn" style="display:none;">순서 저장</button>
    </div>
    <?php endif; ?>

    <div class="timeline-line"></div>
    <div class="timeline-wrapper">
        <div class="timeline-events" id="timeline-sortable">
            <?php foreach ($timeline_items as $index => $item): ?>
            <div class="timeline-item <?php echo ($index % 2 == 0) ? 'top' : 'bottom'; ?>" data-id="<?php echo $item['id']; ?>">
                <a class="item-container" href="#/timeline_detail?id=<?php echo $item['id']; ?>">
                    <div class="item-thumbnail" style="background-image: url('<?php echo htmlspecialchars($item['thumbnail'] ?? ''); ?>');"></div>
                    <div class="item-text">
                        <div class="timeline_year"><?php echo htmlspecialchars($item['year']); ?></div>
                        <div class="timeline_title"><?php echo htmlspecialchars(get_title_from_html($item['full_description'])); ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($is_admin): ?>
<script>
$(document).ready(function() {
    // jQuery UI Sortable 초기화
    $("#timeline-sortable").sortable({
        axis: "x",
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
            $("#save-order-btn").fadeIn();
        }
    }).disableSelection();

    // 순서 저장 버튼 클릭 이벤트
    $('#save-order-btn').off('click').on('click', function() {
        var order = [];
        $('#timeline-sortable .timeline-item').each(function(index) {
            order.push({
                id: $(this).data('id'),
                sort_order: (index + 1) * 10
            });
        });

        $.ajax({
            url: 'timeline_reorder.php', // 순서 저장을 위한 새 핸들러 파일
            type: 'POST',
            data: {
                order: order,
                csrf_token: '<?php echo $csrf_token; ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('순서가 저장되었습니다.');
                    $('#save-order-btn').fadeOut();
                } else {
                    alert('순서 저장 실패: ' + response.message);
                }
            },
            error: function() {
                alert('순서 저장 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>
<?php endif; ?>