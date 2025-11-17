<?php
/*
 * api_admin_give_item.php (★ SQLite 완전 호환 버전)
 */

// ★★★ 1순위: 로그인 인증 ★★★
include 'auth_check.php';
// 2. DB 연결
include 'db_connect.php'; 

// 3. CORS 헤더
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// ... (입력 값 확인 부분은 동일) ...
$input = json_decode(file_get_contents('php://input'), true);
$member_id = $input['member_id'] ?? null;
$item_id = $input['item_id'] ?? null;
$quantity = (int)($input['quantity'] ?? 0);
if (empty($member_id) || empty($item_id) || $quantity <= 0) { /* ... 오류 ... */ exit; }

// 6. 로직: (★ 트랜잭션 ★)
try {
    $pdo->beginTransaction();
    
    // 6-1. 인벤토리에 아이템 추가 (★ SQLite 문법으로 변경 ★)
    $sql_give = "INSERT INTO youth_inventory (member_id, item_id, quantity)
                 VALUES (?, ?, ?)
                 ON CONFLICT(member_id, item_id) DO UPDATE SET quantity = quantity + excluded.quantity";
            
    $stmt = $pdo->prepare($sql_give);
    // (execute의 4번째 파라미터가 필요 없어짐)
    $stmt->execute([$member_id, (int)$item_id, $quantity]);
    
    // 6-2. 아이템 로그 기록
    $reason_item = "관리자 지급";
    $sql_log_item = "INSERT INTO youth_item_logs (member_id, item_id, quantity_change, reason) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql_log_item)->execute([$member_id, (int)$item_id, $quantity, $reason_item]);
    
    $pdo->commit();
    
    $response['message'] = "[{$member_id}] 님에게 [아이템 ID: {$item_id}] {$quantity}개 지급 완료.";

} catch (PDOException $e) {
    $pdo->rollBack();
    $response['status'] = 'error';
    $response['message'] = "DB 오류: " . $e->getMessage();
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>