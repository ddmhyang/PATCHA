<?php
require_once 'includes/db.php';

// URL 파라미터 또는 기본값으로 현재 타임라인 종류를 결정합니다.
$view_type = $_GET['timeline_type'] ?? 'overall';

// 데이터베이스에서 타임라인 아이템과 위치 정보를 가져오는 쿼리입니다.
// 현재 view_type에 맞는 데이터만 선택하여 position_y 기준으로 정렬합니다.
$sql = "SELECT t.*, p.position_y, p.side 
        FROM home2_timeline t
        JOIN home2_timeline_positions p ON t.id = p.timeline_item_id
        WHERE p.timeline_view = ? ";

// 'overall' 뷰가 아닐 경우, 해당 타입의 게시물만 필터링합니다.
if ($view_type !== 'overall') {
    $sql .= " AND t.type = ? ";
}
$sql .= "ORDER BY p.position_y ASC, t.id ASC";

$stmt = $mysqli->prepare($sql);

if ($view_type !== 'overall') {
    $stmt->bind_param("ss", $view_type, $view_type);
} else {
    $stmt->bind_param("s", $view_type);
}

$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 겹침 방지 로직을 위한 변수 초기화
$processed_items = [];
$occupied_space = []; // 각 아이템이 차지하는 공간(y 시작, y 끝, x 오프셋)을 기록
$min_gap = 30; // 아이템 간의 최소 세로 간격 (px)

foreach ($items as $item) {
    // 아이템 종류(dot/interval)에 따라 높이를 결정합니다.
    $item_height = ($item['display_type'] == 'interval') ? intval($item['interval_height']) : 140; // interval은 DB값, dot은 140px
    
    $y = intval($item['position_y']);
    $x_offset = 0; // 수평 이탈 거리

    // 겹침 확인 및 위치 조정 루프
    while (true) {
        $collision = false;
        $current_item_end = $y + $item_height;

        foreach ($occupied_space as $placed) {
            // 현재 아이템과 이미 배치된 아이템의 y축 범위가 최소 간격(min_gap) 이상 겹치는지 확인
            if ($y < $placed['y_end'] + $min_gap && $current_item_end > $placed['y_start'] - $min_gap) {
                // 같은 방향(side)이고 같은 x_offset 레벨에 있으면 충돌로 간주
                if ($item['side'] == $placed['side'] && $x_offset == $placed['x_offset']) {
                    $collision = true;
                    break;
                }
            }
        }

        if ($collision) {
            // 충돌이 발생하면 x_offset을 160px씩 증가시켜 수평으로 밀어냅니다.
            $x_offset += 160;
        } else {
            // 충돌이 없으면 루프를 종료합니다.
            break;
        }
    }

    // 최종 계산된 y 위치와 x 오프셋을 아이템 정보에 추가합니다.
    $item['final_y'] = $y;
    $item['x_offset'] = $x_offset;
    $processed_items[] = $item;

    // 현재 아이템이 차지하는 공간을 기록합니다.
    $occupied_space[] = [
        'y_start' => $y,
        'y_end' => $y + $item_height,
        'side' => $item['side'],
        'x_offset' => $x_offset
    ];
}
?>

<div id="timeline-wrapper" data-view-type="<?php echo htmlspecialchars($view_type); ?>">
    <div id="timeline-line"></div>
    <div class="timeline-items-container">
        <?php foreach ($processed_items as $item): ?>
            <div
                class="timeline-item <?php echo htmlspecialchars($item['side']); ?> item-type-<?php echo htmlspecialchars($item['display_type']); ?> <?php if($is_admin) echo 'draggable'; ?>"
                data-id="<?php echo $item['id']; ?>"
                style="top: <?php echo $item['final_y']; ?>px; --x-offset: <?php echo $item['x_offset']; ?>px;">
                
                <div class="connector-group">
                    <div class="connector"></div>
                    <?php if ($item['display_type'] == 'interval'): ?>
                        <div class="interval-bar" style="height: <?php echo htmlspecialchars($item['interval_height']); ?>px;"></div>
                    <?php else: ?>
                        <div class="dot"></div>
                    <?php endif; ?>
                </div>

                <div class="content-group">
                    <a href="#/timeline_view?id=<?php echo $item['id']; ?>" class="item-link">
                        <div class="chapter"><?php echo htmlspecialchars($item['chapter']); ?></div>
                        <div class="title"><?php echo htmlspecialchars($item['title']); ?></div>
                        <div class="thumbnail" <?php if (!empty($item['thumbnail'])): ?> style="background-image: url('<?php echo htmlspecialchars($item['thumbnail']); ?>');" <?php endif; ?>></div>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>