<?php
/*
 * api_delete_item.php
 * [SPA용 쓰기 API 10]
 * 상점 아이템을 삭제합니다. (ON DELETE CASCADE로 인벤토리에서도 자동 삭제됨)
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

if (empty($item_id)) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(item_id)이 누락되었습니다.';
} else {
    // 6. 로직: 아이템 삭제
    try {
        $sql = "DELETE FROM youth_items WHERE item_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([(int)$item_id]);
        
        // z3rdk9.sql의 ON DELETE CASCADE 설정으로
        // youth_inventory 테이블에서도 이 아이템이 자동으로 삭제됩니다.

        $response['message'] = "[아이템 ID: {$item_id}] (이)가 삭제되었습니다.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>