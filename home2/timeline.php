<?php
require_once 'includes/db.php';

// 어떤 타임라인을 보여줄지 결정 ('overall'이 기본값)
$view_type = $_GET['timeline_type'] ?? 'overall';

$sql = "SELECT t.*, p.position_y, p.side 
        FROM home2_timeline t
        JOIN home2_timeline_positions p ON t.id = p.timeline_item_id
        WHERE p.timeline_view = '" . $mysqli->real_escape_string($view_type) . "' ";

// 개별 타임라인 뷰에서는 해당 타입의 글만 보여주도록 필터링
if ($view_type !== 'overall') {
    $sql .= " AND t.type = '" . $mysqli->real_escape_string($view_type) . "' ";
}

$sql .= "ORDER BY p.position_y ASC, t.id ASC";
$items = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);


// 위치 충돌을 계산하고 최종 위치를 결정하는 로직
$processed_items = [];
$occupied_space = []; // 배치된 아이템의 공간(y_start, y_end, side, x_offset)을 기록하는 배열
$item_height = 180; // 각 아이템의 대략적인 세로 높이 (썸네일 + 텍스트)
$min_gap = 30;      // 아이템 간의 최소 세로 간격

foreach ($items as $item) {
    $y = $item['position_y'];
    $x_offset = 0;

    // 배치될 때까지 y와 x_offset 값을 조정하는 루프
    while (true) {
        $collision = false;
        $current_item_end = $y + $item_height;

        // 이미 배치된 모든 아이템과 공간이 겹치는지 확인
        foreach ($occupied_space as $placed) {
            // Y축 공간이 겹치는지 확인 (최소 간격 포함)
            if ($y < $placed['y_end'] + $min_gap && $current_item_end > $placed['y_start'] - $min_gap) {
                // 같은 방향에 X축 위치까지 겹치는지 확인
                if ($item['side'] == $placed['side'] && $x_offset == $placed['x_offset']) {
                    $collision = true;
                    break;
                }
            }
        }

        if ($collision) {
            // 충돌이 발생하면, X축으로 먼저 밀어내고, 그래도 안되면 Y축을 조정
            if ($x_offset < 120) { // X축으로 최대 3번까지만 밀어냄 (무한루프 방지)
                $x_offset += 300;
            } else {
                $y += $min_gap; // Y축을 30px 아래로 이동
                $x_offset = 0; // Y축이 바뀌었으므로 X축은 다시 처음부터 계산
            }
        } else {
            break; // 충돌이 없으면 위치 확정
        }
    }

    // 최종 위치 정보 저장
    $item['final_y'] = $y;
    $item['x_offset'] = $x_offset;
    $occupied_space[] = [
        'y_start' => $y,
        'y_end' => $y + $item_height,
        'side' => $item['side'],
        'x_offset' => $x_offset
    ];
    $processed_items[] = $item;
}
?>

<div id="timeline-container" data-view-type="<?php echo htmlspecialchars($view_type); ?>">
    <div id="timeline-line"></div>

    <?php foreach ($processed_items as $item): ?>
    <div
        class="timeline-item <?php echo htmlspecialchars($item['side']); ?>"
        data-id="<?php echo $item['id']; ?>"
        style="top: <?php echo $item['final_y']; ?>px; --x-offset: <?php echo $item['x_offset']; ?>px;"
    >
        <div class="timeline-dot"></div>
        <div class="timeline-connector"></div>

        <div class="timeline-item-content <?php if($is_admin) echo 'draggable'; ?>">
            <a href="#/timeline_view?id=<?php echo $item['id']; ?>" class="item-link">
                <?php if(!empty($item['thumbnail'])): ?>
                <?php endif; ?>
                <div class="item-text">
                    <span class="chapter-name"><?php echo htmlspecialchars($item['chapter']); ?></span>
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                </div>
                <div class="thumbnail" style="background-image: url('<?php echo htmlspecialchars($item['thumbnail']); ?>');"></div>
            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if ($is_admin): ?>
    <button id="add-item-btn" onclick="window.location.hash='#/timeline_form'">새 글 작성</button>
    <?php endif; ?>
</div>