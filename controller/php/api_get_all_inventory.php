<?php
/*
 * api_get_all_inventory.php
 * [SPA용 읽기 API 5]
 * 모든 회원의 인벤토리 목록을 JSON으로 반환합니다. (JOIN 사용)
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

try {
    // 4. 로직: 3개 테이블 JOIN
    // (인벤토리, 회원, 아이템 테이블을 합쳐서 이름까지 가져옴)
    $sql = "SELECT 
                T1.member_id, 
                T1.item_id, 
                T1.quantity,
                T2.member_name,
                T3.item_name
            FROM youth_inventory AS T1
            LEFT JOIN youth_members AS T2 ON T1.member_id = T2.member_id
            LEFT JOIN youth_items AS T3 ON T1.item_id = T3.item_id
            ORDER BY T2.member_name, T3.item_name"; // 회원-아이템 이름순 정렬
            
    $stmt = $pdo->query($sql);
    $logs_list = $stmt->fetchAll();
    
    // 회원이 삭제(NULL)되거나 아이템이 삭제(NULL)된 경우 필터링
    $response['data'] = array_filter($logs_list, function($item) {
        return $item['member_name'] !== null && $item['item_name'] !== null;
    });

} catch (Exception $e) {
    // 5. 실패 응답
    $response['status'] = 'error';
    $response['message'] = '인벤토리 조회 실패: ' . $e->getMessage();
}

// 6. 최종 JSON 응답
echo json_encode($response);
?>