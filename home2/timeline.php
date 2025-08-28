<?php
require_once 'includes/db.php';

$view_type = $_GET['timeline_type'] ?? 'overall';

$sql = "SELECT t.*, p.position_y, p.side 
        FROM home2_timeline t
        JOIN home2_timeline_positions p ON t.id = p.timeline_item_id
        WHERE p.timeline_view = '" . $mysqli->real_escape_string($view_type) . "' ";

if ($view_type !== 'overall') {
    $sql .= " AND t.type = '" . $mysqli->real_escape_string($view_type) . "' ";
}
$sql .= "ORDER BY p.position_y ASC, t.id ASC";
$items = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);

$processed_items = [];
$occupied_space = [];
$item_height = 180;
$min_gap = 30;

foreach ($items as $item) {
    $item_height = ($item['display_type'] == 'interval') ? intval($item['interval_height']) : 180;
    
    $y = $item['position_y'];
    $x_offset = 0;
    $original_y = $y;

    while (true) {
        $collision = false;
        $current_item_end = $y + $item_height;
        foreach ($occupied_space as $placed) {
            if ($y < $placed['y_end'] + $min_gap && $current_item_end > $placed['y_start'] - $min_gap) {
                if ($item['side'] == $placed['side'] && $x_offset == $placed['x_offset']) {
                    $collision = true;
                    break;
                }
            }
        }
        if ($collision) {
            if ($x_offset < 120) { $x_offset += 160; } 
            else { $y += $min_gap; $x_offset = 0; }
        } else {
            break;
        }
    }

    if ($original_y != $y) { $positions_to_update[$item['id']] = $y; }
    $item['final_y'] = $y;
    $item['x_offset'] = $x_offset;
    $occupied_space[] = [
        'y_start' => $y, 'y_end' => $y + $item_height,
        'side' => $item['side'], 'x_offset' => $x_offset
    ];
    $processed_items[] = $item;
}

if (!empty($positions_to_update) && $is_admin) {
    $update_query = "UPDATE home2_timeline_positions SET position_y = CASE timeline_item_id ";
    foreach ($positions_to_update as $id => $y) {
        $update_query .= "WHEN " . intval($id) . " THEN " . intval($y) . " ";
    }
    $update_query .= "END WHERE timeline_item_id IN (" . implode(',', array_keys($positions_to_update)) . ") AND timeline_view = '" . $mysqli->real_escape_string($view_type) . "'";
    $mysqli->query($update_query);
}
?>

<div
    id="timeline-container"
    data-view-type="<?php echo htmlspecialchars($view_type); ?>">

    <?php 
    $total_items = count($processed_items);
    foreach ($processed_items as $index => $item): 
        $z_index = $total_items - $index;
    ?>
    <div
        class="timeline-item <?php echo htmlspecialchars($item['side']); ?> item-type-<?php echo htmlspecialchars($item['display_type']); ?>"
        data-id="<?php echo $item['id']; ?>"
        style="top: <?php echo $item['final_y']; ?>px; --x-offset: <?php echo $item['x_offset']; ?>px; z-index: <?php echo $z_index; ?>;">
        <div class="connector-group">

            <?php 
            $connector_style = '';
            if ($item['display_type'] == 'interval') {
                // 간격 높이의 절반에서 연결선 자체 높이(3px)의 절반을 빼서 정확한 중앙점을 계산합니다.
                $connector_top = (($item['interval_height'] ?? 150) / 2) - 1.5;
                $connector_style = 'style="top: ' . $connector_top . 'px;"';
            }
            ?>
            <div class="connector" <?php echo $connector_style; ?>></div>
            <?php if ($item['display_type'] == 'interval'): ?>
            <div
                class="interval-bar"
                style="height: <?php echo htmlspecialchars($item['interval_height'] ?? 150); ?>px;"></div>
        <?php else: ?>
            <div class="dot"></div>
            <?php endif; ?>
        </div>

        <div class="content-group <?php if($is_admin) echo 'draggable'; ?>">
            <a href="#/timeline_view?id=<?php echo $item['id']; ?>" class="item-link">
                <div class="chapter"><?php echo htmlspecialchars($item['chapter']); ?></div>
                <div class="title"><?php echo htmlspecialchars($item['title']); ?></div>

                <div
                    class="thumbnail"
                    <?php if (!empty($item['thumbnail'])): ?>
                    style="background-image: url('<?php echo htmlspecialchars($item['thumbnail']); ?>');"
                    <?php endif; ?>></div>

            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if ($is_admin): ?>
    <button id="add-item-btn" onclick="window.location.hash='#/timeline_form'">새 글 작성</button>
    <?php endif; ?>
</div>