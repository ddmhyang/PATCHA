<?php
// admin_gamble.php : 도박 배율 및 확률 설정 (룰렛)
require_once 'common.php';

// 권한 체크
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('관리자만 접근 가능합니다.'); location.replace('index.php');</script>";
    exit;
}

// [중요] 테이블 자동 생성 (Setup에서 누락되었을 경우 대비)
try {
    sql_exec("CREATE TABLE IF NOT EXISTS School_Gamble_Config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        ratio FLOAT NOT NULL COMMENT '배율',
        probability INT NOT NULL COMMENT '확률(%)'
    )");
} catch(Exception $e) {}

// 1. 룰렛 항목 추가
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name']);
    $ratio = (float)$_POST['ratio'];
    $prob = to_int($_POST['prob']);
    
    if ($name && $prob > 0) {
        sql_exec("INSERT INTO School_Gamble_Config (name, ratio, probability) VALUES (?, ?, ?)", 
            [$name, $ratio, $prob]
        );
        echo "<script>location.replace('admin_gamble.php');</script>";
        exit;
    }
}

// 2. 항목 삭제
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = to_int($_POST['id']);
    sql_exec("DELETE FROM School_Gamble_Config WHERE id=?", [$id]);
    echo "<script>location.replace('admin_gamble.php');</script>";
    exit;
}

// 목록 조회
$list = sql_fetch_all("SELECT * FROM School_Gamble_Config ORDER BY ratio ASC");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>도박장 설정</title>
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #CE5961; --bg: #F0F2F5; }
        body { font-family: 'Pretendard', sans-serif; background: var(--bg); margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h2 { margin: 0; color: #333; display: flex; align-items: center; gap: 10px; }
        .back-btn { font-size: 14px; background: #999; border: none; color: white; border-radius: 5px; padding: 8px 15px; cursor: pointer; font-weight: bold; }

        /* 입력 폼 */
        .form-box { background: white; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-row { margin-bottom: 15px; }
        label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #555; }
        input { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; box-sizing: border-box; font-family: 'Pretendard'; }
        
        .btn-add { padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; }
        .btn-add:hover { opacity: 0.9; }

        /* 리스트 */
        .row { background: white; padding: 15px; margin-bottom: 10px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .info b { font-size: 18px; color: #333; }
        .info span { font-size: 14px; color: #666; margin-left: 5px; }
        .btn-del { background: #fee; color: #e74c3c; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
        .btn-del:hover { background: #fdd; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2><i class="fa-solid fa-dice"></i> 룰렛 설정</h2>
        <button class="back-btn" onclick="location.href='index.php'">메인으로</button>
    </div>

    <form method="POST" style="background:#f9f9f9; padding:15px; border-radius:10px; margin-bottom:20px;">
        <input type="hidden" name="action" value="create">
        
        <div style="margin-bottom:10px;">
            <label>항목 이름</label>
            <input type="text" name="name" placeholder="예: 대박, 쪽박, 꽝" required>
        </div>
        
        <div style="margin-bottom:15px;">
            <label>배율 (x)</label>
            <input type="text" name="ratio" placeholder="예: 2 (2배), -1 (몰수), -5 (빚쟁이)" required>
            <div style="font-size:12px; color:#888; margin-top:5px;">
                * 양수: 획득 (건 돈 * 배율만큼 추가)<br>
                * 0: 변화 없음<br>
                * 음수: 손실 (건 돈 * 배율만큼 차감 -> 포인트가 마이너스가 될 수 있음)
            </div>
        </div>
        
        <input type="hidden" name="prob" value="1"> <button type="submit" class="btn-add">룰렛 항목 추가</button>
    </form>

    <div style="font-size:14px; color:#666; margin-bottom:10px; margin-left:5px;">현재 등록된 항목</div>
    <?php foreach($list as $row): ?>
        <div class="row">
            <div class="info">
                <b><?=$row['name']?></b>
                <span>(x<?=$row['ratio']?> / <?=$row['probability']?>%)</span>
            </div>
            <form method="POST" onsubmit="return confirm('삭제하시겠습니까?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=$row['id']?>">
                <button type="submit" class="btn-del"><i class="fa-solid fa-trash"></i></button>
            </form>
        </div>
    <?php endforeach; ?>
    
    <?php if(!$list): ?>
        <div style="text-align:center; padding:30px; color:#999;">등록된 항목이 없습니다.<br>룰렛 게임을 위해 최소 1개 이상 등록해주세요.</div>
    <?php endif; ?>
</div>

</body>
</html>