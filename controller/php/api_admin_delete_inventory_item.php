<?php
/*
 * api_admin_delete_inventory_item.php
 * [SPA용 쓰기 API 7]
 * 회원의 특정 아이템을 인벤토리에서 삭제합니다.
 * (★ 아이템 로그 기록 기능 추가됨, 트랜잭션으로 변경됨)
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

// 4. 입력 데이터 받기 (POST)
$input = json_decode(file_get_contents('php://input'), true);

$response = ['status' => 'success'];

// 5. 입력 값 확인 (복합키 2개)
$member_id = $input['member_id'] ?? null;
$item_id = $input['item_id'] ?? null;

if (empty($member_id) || empty($item_id)) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(member_id, item_id)이 누락되었습니다.';
} else {
    // 6. 로직: (★ 트랜잭션으로 변경 ★)
    try {
        $pdo->beginTransaction();

        // 6-1. (★신규★) 삭제 전, 수량 먼저 조회 (로그 기록용)
        $sql_get = "SELECT quantity FROM youth_inventory WHERE member_id = ? AND item_id = ? FOR UPDATE";
        $stmt_get = $pdo->prepare($sql_get);
        $stmt_get->execute([$member_id, (int)$item_id]);
        $item = $stmt_get->fetch();
        
        $deleted_quantity = 0;
        if ($item) {
            $deleted_quantity = (int)$item['quantity'];
        }

        if ($deleted_quantity <= 0) {
            throw new Exception("삭제할 아이템이 없습니다. (이미 0개)");
        }
        
        // 6-2. 인벤토리에서 아이템 삭제
        $sql_delete = "DELETE FROM youth_inventory WHERE member_id = ? AND item_id = ?";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute([$member_id, (int)$item_id]);
        
        // 6-3. (★신규★) 아이템 로그 기록 (-수량)
        $reason_item = "관리자가 회수/삭제";
        $sql_log_item = "INSERT INTO youth_item_logs (member_id, item_id, quantity_change, reason) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql_log_item)->execute([$member_id, (int)$item_id, -$deleted_quantity, $reason_item]);
        
        $pdo->commit();
        
        $response['message'] = "[{$member_id}] 님의 [아이템 ID: {$item_id}] (수량: {$deleted_quantity}개) 삭제 완료.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>