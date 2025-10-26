<?php
require_once 'includes/db.php';

$view_type = $_GET['timeline_type'] ?? 'overall';

$sql = "SELECT t.*, p.position_y, p.side 
        FROM home2_timeline t
        JOIN home2_timeline_positions p ON t.id = p.timeline_item_id
        WHERE p.timeline_view = ? ";

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

$processed_items = [];
$occupied_space = [];
$min_gap = 30;

foreach ($items as $item) {
    $item_height = ($item['display_type'] == 'interval') ? intval($item['interval_height']) : 140;
    $y = intval($item['position_y']);
    $x_offset = 0;

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
            $x_offset += 160;
        } else {
            break;
        }
    }

    $item['final_y'] = $y;
    $item['x_offset'] = $x_offset;
    $processed_items[] = $item;

    $occupied_space[] = [
        'y_start' => $y, 'y_end' => $y + $item_height,
        'side' => $item['side'], 'x_offset' => $x_offset
    ];
}
?>

<div id="timeline-wrapper" data-view-type="<?php echo htmlspecialchars($view_type); ?>">
    <div id="timeline-line"></div>

    <div class="timeline-items-container">
        <?php foreach ($processed_items as $item): ?>
            <div
                class="timeline-item <?php echo htmlspecialchars($item['side']); ?> <?php if($is_admin) echo 'draggable'; ?>"
                data-id="<?php echo $item['id']; ?>"
                style="top: <?php echo $item['final_y']; ?>px;">
                
                <div class="item-handle">
                    <?php if ($item['display_type'] == 'interval'): ?>
                        <div class="interval-bar" style="height: <?php echo htmlspecialchars($item['interval_height']); ?>px;"></div>
                    <?php else: ?>
                        <div class="dot"></div>
                    <?php endif; ?>
                </div>

                <div class="item-content-wrapper" style="--x-offset: <?php echo $item['x_offset']; ?>px;">
                    <div class="connector"></div>
                    <div class="content-group">
                        <a href="#/timeline_view?id=<?php echo $item['id']; ?>" class="item-link">
                            <div class="chapter"><?php echo htmlspecialchars($item['chapter']); ?></div>
                            <div class="title"><?php echo htmlspecialchars($item['title']); ?></div>
                            <div class="thumbnail" <?php if (!empty($item['thumbnail'])): ?> style="background-image: url('<?php echo htmlspecialchars($item['thumbnail']); ?>');" <?php endif; ?>></div>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>