<?php
/*
 * api_get_member_inventory.php
 * [SPA용 읽기 API 6]
 * 특정 회원이 보유한 아이템 목록과 수량을 JSON으로 반환합니다.
 */

// ★★★ 1순위: 로그인 인증 ★★★
include 'auth_check.php';
// 2. DB 연결
include 'db_connect.php'; 

// 3. CORS 헤더
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

$response = ['status' => 'success', 'data' => []];

// 4. 입력 값 확인 (GET 방식)
$member_id = $_GET['member_id'] ?? null;

if (empty($member_id)) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(member_id)이 누락되었습니다.';
} else {
    try {
        // 5. 로직: 인벤토리 JOIN (특정 회원)
        $sql = "SELECT 
                    T1.item_id, 
                    T1.quantity,
                    T2.item_name
                FROM youth_inventory AS T1
                JOIN youth_items AS T2 ON T1.item_id = T2.item_id
                WHERE T1.member_id = ?
                ORDER BY T2.item_name";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id]);
        $inventory_list = $stmt->fetchAll();
        
        $response['data'] = $inventory_list;

    } catch (Exception $e) {
        $response['status'] = 'error';
        $response['message'] = '인벤토리 조회 실패: ' . $e->getMessage();
    }
}

// 6. 최종 JSON 응답
echo json_encode($response);
?>