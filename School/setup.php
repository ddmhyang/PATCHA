<?php
// setup.php : 학교 생존 RPG DB 완전 초기화 (로그 테이블 포함)
session_start();

if (file_exists('config.php') && !isset($_GET['force'])) {
    header("Location: index.php");
    exit;
}

$msg = "";

if (isset($_POST['run_setup'])) {
    $host = $_POST['db_host'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $dbname = $_POST['db_name'];
    $admin_pw = $_POST['admin_pw'];

    $config_code = "<?php\n";
    $config_code .= "define('MS_HOST', '$host');\n";
    $config_code .= "define('MS_DB', '$dbname');\n";
    $config_code .= "define('MS_USER', '$user');\n";
    $config_code .= "define('MS_PASS', '$pass');\n";
    $config_code .= "function get_conn() {\n";
    $config_code .= "    try {\n";
    $config_code .= "        \$pdo = new PDO(\"mysql:host=\".MS_HOST.\";dbname=\".MS_DB.\";charset=utf8mb4\", MS_USER, MS_PASS);\n";
    $config_code .= "        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
    $config_code .= "        return \$pdo;\n";
    $config_code .= "    } catch (PDOException \$e) {\n";
    $config_code .= "        return null;\n";
    $config_code .= "    }\n";
    $config_code .= "}\n";
    $config_code .= "?>";

    file_put_contents('config.php', $config_code);

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        $pdo->exec("SET time_zone = '+09:00'"); // 설치 시에도 시간대 설정

        // 테이블 초기화
        $tables = [
            'School_Members', 'School_Item_Info', 'School_Inventory', 
            'School_Status_Info', 'School_Status_Active', 'School_Monsters', 
            'School_Battles', 'School_Shop_Config', 'School_Gamble_Config', 'School_Log'
        ];
        foreach($tables as $tbl) { $pdo->exec("DROP TABLE IF EXISTS $tbl"); }

// 1. 멤버
        $pdo->exec("CREATE TABLE School_Members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL UNIQUE,
            pw VARCHAR(255) NOT NULL,
            name VARCHAR(50) NOT NULL,
            role ENUM('admin','user') DEFAULT 'user',
            level INT DEFAULT 1, exp INT DEFAULT 0, point INT DEFAULT 0,
            hp_max INT DEFAULT 100, hp_current INT DEFAULT 100,
            stat_str INT DEFAULT 10, stat_dex INT DEFAULT 10, stat_con INT DEFAULT 10,
            stat_int INT DEFAULT 10, stat_luk INT DEFAULT 10, stat_points INT DEFAULT 0,
            image_url TEXT, last_action_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // 2. 아이템
        $pdo->exec("CREATE TABLE School_Item_Info (
            item_id INT AUTO_INCREMENT PRIMARY KEY,
            type ENUM('CONSUME','WEAPON','ARMOR','ETC') NOT NULL,
            name VARCHAR(100) NOT NULL,
            descr TEXT, hidden_descr TEXT, price INT DEFAULT 100,
            max_dur INT DEFAULT 0, img_icon VARCHAR(100) DEFAULT '<i class=\"fa-solid fa-box\"></i>',
            effect_data JSON, is_gacha BOOLEAN DEFAULT 0
        )");

        // 3. 인벤토리
        $pdo->exec("CREATE TABLE School_Inventory (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner_id INT NOT NULL, item_id INT NOT NULL,
            count INT DEFAULT 1, is_equipped BOOLEAN DEFAULT 0, cur_dur INT DEFAULT 0,
            KEY (owner_id)
        )");

        // 4. 상태이상 (stage_config에 수치 데이터 포함)
        $pdo->exec("CREATE TABLE School_Status_Info (
            status_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            max_stage INT DEFAULT 3,
            stage_config JSON COMMENT '단계별 시간/설명/수치데이터'
        )");

        // 5. 활성 상태이상
        $pdo->exec("CREATE TABLE School_Status_Active (
            id INT AUTO_INCREMENT PRIMARY KEY,
            target_id INT NOT NULL, status_id INT NOT NULL,
            current_stage INT DEFAULT 1,
            infected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_evolved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY (target_id)
        )");

        // 6. 몬스터
        $pdo->exec("CREATE TABLE School_Monsters (
            mob_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL, stats JSON, skills JSON, drop_items JSON,
            give_exp INT DEFAULT 10, give_point INT DEFAULT 10, defeat_penalty JSON
        )");

        // 7. 전투
        $pdo->exec("CREATE TABLE School_Battles (
            room_id INT AUTO_INCREMENT PRIMARY KEY,
            host_id INT NOT NULL, status ENUM('WAITING','FIGHTING','ENDED') DEFAULT 'WAITING',
            mob_live_data JSON, players_data JSON, battle_log JSON,
            turn_status VARCHAR(20) DEFAULT 'player', enemy_roll INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // 8. 상점
        $pdo->exec("CREATE TABLE School_Shop_Config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_id INT NOT NULL, stock INT DEFAULT -1,
            refill_type ENUM('NONE', 'DAILY', 'TIME') DEFAULT 'NONE',
            refill_value VARCHAR(50), refill_amount INT DEFAULT 0,
            last_refilled_at DATETIME DEFAULT CURRENT_TIMESTAMP, display_order INT DEFAULT 0
        )");

        // 9. 도박
        $pdo->exec("CREATE TABLE School_Gamble_Config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL, ratio FLOAT NOT NULL, probability INT NOT NULL
        )");

        // 10. [추가] 로그
        $pdo->exec("CREATE TABLE School_Log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY (user_id)
        )");

        // 초기 데이터
        $hashed_pw = password_hash($admin_pw, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO School_Members (user_id, pw, name, role, point, stat_str, stat_dex, stat_con, stat_int, stat_luk) VALUES (?, ?, ?, 'admin', 99999, 99, 99, 99, 99, 99)")->execute(['admin', $hashed_pw, '학생회장']);

        // 기본 아이템
        $pdo->exec("INSERT INTO School_Item_Info (type, name, descr, price, max_dur, img_icon, effect_data) VALUES 
            ('CONSUME', '매점 빵', '체력을 30 회복합니다.', 50, 0, '<i class=\"fa-solid fa-bread-slice\"></i>', '{\"hp_heal\": 30}'),
            ('WEAPON', '대걸레 자루', '흔한 막대기. (내구도 20)', 1000, 20, '<i class=\"fa-solid fa-baseball-bat-ball\"></i>', '{\"atk\": 5}'),
            ('ARMOR', '체육복', '활동하기 편하다. (내구도 30)', 1500, 30, '<i class=\"fa-solid fa-shirt\"></i>', '{\"def\": 2}')
        ");

        // 기본 몬스터
        $pdo->exec("INSERT INTO School_Monsters (name, stats, skills, drop_items, give_exp, give_point, defeat_penalty) VALUES 
            ('배회하는 좀비', '{\"hp\": 50, \"atk\": 10, \"def\": 0, \"speed\": 5, \"str\":10, \"dex\":5, \"con\":5, \"int\":1, \"luk\":1}', '[]', '{\"1\": 50}', 20, 100, '{\"point\": -50}')
        ");

        $msg = "<div class='success'>✅ 설치 완료! <a href='index.php'>[게임 시작하기]</a></div>";

    } catch (PDOException $e) {
        $msg = "<div class='error'>🚫 오류: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School RPG Setup</title>
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Pretendard', sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        .icon-box { font-size: 50px; color: #CE5961; margin-bottom: 20px; }
        h2 { margin: 0 0 20px 0; color: #333; }
        input { 
            width: 100%; padding: 15px; margin: 8px 0; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; font-family: inherit;
        }
        input:focus { border-color: #CE5961; outline: none; }
        button { 
            width: 100%; padding: 15px; background: #CE5961; color: white; border: none; border-radius: 12px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 20px; transition: background-color 0.2s;
        }
        button:hover { background: #b0464d; }
        .success { color: #2ecc71; background: #e8f8f5; padding: 15px; border-radius: 10px; margin-top: 20px; font-weight: bold;}
        .error { color: #e74c3c; background: #fdedec; padding: 15px; border-radius: 10px; margin-top: 20px; font-weight: bold;}
        hr { border: 0; border-top: 1px solid #eee; margin: 20px 0; }
    </style>
</head>
<body>
    <?=$msg?>
    <?php if(!$msg): ?>
    <form method="POST">
        <input type="text" name="db_host" placeholder="host" value="localhost"><br>
        <input type="text" name="db_user" placeholder="user"><br>
        <input type="password" name="db_pass" placeholder="pass"><br>
        <input type="text" name="db_name" placeholder="dbname" value="school_rpg"><br>
        <input type="password" name="admin_pw" placeholder="admin pw"><br>
        <button type="submit" name="run_setup">설치</button>
    </form>
    <?php endif; ?>
</body>
</html>