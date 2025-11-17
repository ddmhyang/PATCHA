<?php
/*
 * api_get_all_items.php
 * [SPA용 읽기 API 2]
 * 모든 상점 아이템 목록을 JSON으로 반환합니다.
 */

// 1. CORS 헤더 설정
include 'auth_check.php'; 
// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// 2. DB 연결
include 'db_connect.php'; // $pdo

$response = ['status' => 'success', 'data' => []];

try {
    // 3. 로직: 전체 아이템 목록 조회 (최근 등록순)
    $sql = "SELECT item_id, item_name, item_description, price, stock, status 
            FROM youth_items 
            ORDER BY item_id DESC";
    $stmt = $pdo->query($sql);
    $items_list = $stmt->fetchAll();
    
    $response['data'] = $items_list;

} catch (Exception $e) {
    // 4. 실패 응답
    $response['status'] = 'error';
    $response['message'] = '아이템 목록 조회 실패: ' . $e->getMessage();
}

// 5. 최종 JSON 응답
echo json_encode($response);
?>