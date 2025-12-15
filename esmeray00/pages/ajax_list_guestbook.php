<?php
require_once "../includes/db.php";

// $conn 대신 $mysqli 사용
$sql = "SELECT * FROM esmeray_guestbook ORDER BY id DESC";
$result = $mysqli->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        // data-id 속성 추가 (삭제 기능용)
        echo '<div class="guestbook-item" data-id="' . $row['id'] . '">';
        echo '  <div class="gb-header">';
        echo '      <span class="gb-name">' . htmlspecialchars($row['name']) . '</span>';
        echo '      <span>' . substr($row['date'], 0, 16) . '</span>';
        echo '  </div>';
        echo '  <div class="gb-content">' . nl2br(htmlspecialchars($row['content'])) . '</div>';
        echo '</div>';
    }
} else {
    echo "데이터를 불러오는 중 오류가 발생했습니다.";
}
?>