<?php
/*
 * api_admin_give_item.php
 * [SPA용 쓰기 API 6]
 * 관리자가 회원에게 아이템을 지급합니다.
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

// 5. 입력 값 확인
$member_id = $input['member_id'] ?? null;
$item_id = $input['item_id'] ?? null;
$quantity = (int)($input['quantity'] ?? 0);

if (empty($member_id) || empty($item_id) || $quantity <= 0) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(member_id, item_id)이 없거나 수량이 0 이하입니다.';
} else {
    // 6. 로직: 인벤토리에 아이템 추가 (INSERT ... ON DUPLICATE KEY UPDATE)
    try {
        $sql = "INSERT INTO youth_inventory (member_id, item_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";
                
        $stmt = $pdo->prepare($sql);
        // (member_id, item_id, quantity, quantity)
        $stmt->execute([$member_id, (int)$item_id, $quantity, $quantity]);
        
        $response['message'] = "[{$member_id}] 님에게 [아이템 ID: {$item_id}] {$quantity}개 지급 완료.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>