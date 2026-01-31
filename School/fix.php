<?php
// fix_battle_db.php : 전투 시스템에 필요한 DB 컬럼 추가
require_once 'common.php'; // DB 연결 설정 로드

try {
    // 1. School_Battles 테이블 업데이트
    echo "<h3>🛠️ School_Battles 테이블 구조 업데이트 중...</h3>";
    
    $alter_queries = [
        "ADD COLUMN guest_id INT DEFAULT 0",
        "ADD COLUMN target_id INT DEFAULT 0",
        "ADD COLUMN mob_live_data LONGTEXT",
        "ADD COLUMN players_data LONGTEXT",
        "ADD COLUMN battle_log LONGTEXT",
        "ADD COLUMN turn_status VARCHAR(50) DEFAULT 'ready'",
        "ADD COLUMN enemy_roll INT DEFAULT 0",
        "ADD COLUMN current_turn_id INT DEFAULT 0"
    ];

    foreach ($alter_queries as $sql) {
        try {
            // 컬럼 추가 시도
            $pdo->exec("ALTER TABLE School_Battles " . $sql);
            echo "<div style='color:green'>[성공] $sql</div>";
        } catch (PDOException $e) {
            // 이미 컬럼이 존재하면 오류가 발생하므로 무시 (Duplicate column name)
            if ($e->getCode() == '42S21') {
                echo "<div style='color:gray'>[패스] 이미 존재하는 컬럼입니다. (" . explode(' ', $sql)[2] . ")</div>";
            } else {
                echo "<div style='color:red'>[오류] $sql : " . $e->getMessage() . "</div>";
            }
        }
    }

    // 2. School_Status_Active 테이블 생성 (상태이상용)
    echo "<br><h3>🛠️ 상태이상 테이블 점검 중...</h3>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS School_Status_Active (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target_id INT NOT NULL,
        status_id INT NOT NULL,
        current_stage INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_evolved_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<div style='color:green'>[성공] School_Status_Active 테이블 확인 완료</div>";

    // 3. School_Status_Info 테이블 생성 (상태이상 정보용)
    $pdo->exec("CREATE TABLE IF NOT EXISTS School_Status_Info (
        status_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50),
        max_stage INT DEFAULT 3,
        stage_config TEXT
    )");
    echo "<div style='color:green'>[성공] School_Status_Info 테이블 확인 완료</div>";

    // 4. School_Gamble_Config 테이블 생성 (도박용)
    $pdo->exec("CREATE TABLE IF NOT EXISTS School_Gamble_Config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        ratio DECIMAL(5,2) NOT NULL DEFAULT 2.0
    )");
    echo "<div style='color:green'>[성공] School_Gamble_Config 테이블 확인 완료</div>";

    echo "<hr><h2>✅ 모든 패치가 완료되었습니다!</h2>";
    echo "<a href='index.php'>[메인으로 돌아가기]</a>";

} catch (Exception $e) {
    die("<h2 style='color:red'>치명적 오류 발생: " . $e->getMessage() . "</h2>");
}
?>