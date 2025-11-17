<?php
/*
 * api_update_item.php
 * [SPA용 쓰기 API 9]
 * 기존 상점 아이템을 수정합니다.
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
$item_id = $input['item_id'] ?? null;
$item_name = $input['item_name'] ?? null;
$item_description = $input['item_description'] ?? null;
$price = $input['price'] ?? null;
$stock = $input['stock'] ?? null;
$status = $input['status'] ?? null;

if (empty($item_id) || empty($item_name) || $price === null || $stock === null || empty($status)) {
    $response['status'] = 'error';
    $response['message'] = '필수 값이 누락되었습니다.';
} else {
    // 6. 로직: 아이템 정보 업데이트
    try {
        $sql = "UPDATE youth_items 
                SET item_name = ?, item_description = ?, price = ?, stock = ?, status = ?
                WHERE item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $item_name, 
            $item_description, 
            (int)$price, 
            (int)$stock, 
            $status, 
            (int)$item_id
        ]);
        
        $response['message'] = "아이템 [{$item_name}] (이)가 수정되었습니다.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>