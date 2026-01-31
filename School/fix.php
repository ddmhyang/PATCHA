<?php
// fix.php : 새로운 장비 타입 아이템 데이터 추가 및 DB 점검
include 'config.php'; // 설정 파일 로드

try {
    $pdo = new PDO("mysql:host=".MS_HOST.";dbname=".MS_DB.";charset=utf8mb4", MS_USER, MS_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h3>🛠️ 데이터베이스 패치 중...</h3>";

    // 1. 새로운 장비 타입의 아이템들이 존재하는지 확인하고 없으면 추가합니다.
    // 추가할 아이템 목록
    $new_items = [
        ['name' => '초보자 모자', 'type' => 'HAT', 'price' => 50, 'descr' => '평범한 야구모자입니다.', 'eff' => '{"def":1}'],
        ['name' => '검은 마스크', 'type' => 'FACE', 'price' => 50, 'descr' => '얼굴을 가려주는 마스크.', 'eff' => '{"luk":1}'],
        ['name' => '학교 체육복(상)', 'type' => 'TOP', 'price' => 100, 'descr' => '활동하기 편한 체육복 상의.', 'eff' => '{"def":2, "hp_max":10}'],
        ['name' => '학교 체육복(하)', 'type' => 'BOTTOM', 'price' => 100, 'descr' => '활동하기 편한 체육복 하의.', 'eff' => '{"def":2, "speed":1}'],
        ['name' => '목장갑', 'type' => 'GLOVES', 'price' => 30, 'descr' => '미끄럼 방지 장갑.', 'eff' => '{"str":1}'],
        ['name' => '실내화', 'type' => 'SHOES', 'price' => 30, 'descr' => '학교 매점에서 파는 실내화.', 'eff' => '{"speed":2}']
    ];

    foreach ($new_items as $it) {
        $stmt = $pdo->prepare("SELECT count(*) FROM School_Item_Info WHERE name = ?");
        $stmt->execute([$it['name']]);
        if ($stmt->fetchColumn() == 0) {
            $ins = $pdo->prepare("INSERT INTO School_Item_Info (name, type, price, descr, max_dur, img_icon, effect_data) VALUES (?, ?, ?, ?, 100, '<i class=\"fa-solid fa-shirt\"></i>', ?)");
            $ins->execute([$it['name'], $it['type'], $it['price'], $it['descr'], $it['eff']]);
            echo "추가됨: {$it['name']} ({$it['type']})<br>";
        }
    }
    
    // 2. 상점 설정에도 자동으로 추가 (재고 무제한)
    // 방금 추가된 아이템들을 상점에 등록
    $pdo->exec("INSERT INTO School_Shop_Config (item_id, stock) 
                SELECT item_id, -1 FROM School_Item_Info 
                WHERE item_id NOT IN (SELECT item_id FROM School_Shop_Config)");

    echo "<hr><h3 style='color:green;'>패치 완료! 게임을 즐기세요.</h3>";
    echo "<a href='index.php'>메인으로 돌아가기</a>";

} catch (PDOException $e) {
    die("오류 발생: " . $e->getMessage());
}
?>