<?php
/*
 * api_admin_delete_inventory_item.php
 * [SPA용 쓰기 API 7]
 * 회원의 특정 아이템을 인벤토리에서 삭제합니다.
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
    // 6. 로직: 인벤토리에서 아이템 삭제
    try {
        // (PK가 2개이므로 WHERE 조건 2개 필요)
        $sql = "DELETE FROM youth_inventory WHERE member_id = ? AND item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id, (int)$item_id]);
        
        $response['message'] = "[{$member_id}] 님의 [아이템 ID: {$item_id}] 삭제 완료.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>