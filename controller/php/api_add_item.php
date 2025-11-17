<?php
/*
 * api_add_item.php
 * [SPA용 쓰기 API 2]
 * 새 상점 아이템을 등록합니다.
 */

// 1. CORS 헤더 설정
include 'auth_check.php'; 
// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // '쓰기'는 POST
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// 2. DB 연결
include 'db_connect.php'; // $pdo

// 3. 입력 데이터 받기 (POST 방식)
$input = json_decode(file_get_contents('php://input'), true);

$response = ['status' => 'success']; // 기본 성공 응답

// 4. 입력 값 확인 및 기본값 설정
$item_name = $input['item_name'] ?? '';
$item_description = $input['item_description'] ?? '';
$price = (int)($input['price'] ?? 0);
$stock = (int)($input['stock'] ?? -1);
$status = $input['status'] ?? 'selling';

if (empty($item_name) || $price < 0 || $stock < -1) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(아이템 이름, 가격 0 이상, 재고 -1 이상)이 잘못되었습니다.';

} else {
    // 5. 로직: 새 아이템 등록
    try {
        $sql = "INSERT INTO youth_items (item_name, item_description, price, stock, status) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$item_name, $item_description, $price, $stock, $status]);
        
        $response['message'] = "아이템 [{$item_name}] 이(가) 등록되었습니다.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 6. 최종 JSON 응답
echo json_encode($response);
?>