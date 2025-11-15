<?php
/*
 * get_user_info.php
 * 특정 회원의 정보(포인트, 인벤토리)를 조회합니다.
 */

// 1. CORS 헤더 설정 (★매우 중요★)
include 'auth_check.php'; 
// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS"); // '읽기' 전용이므로 GET만 허용
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8"); // JSON으로 응답

// 2. DB 연결 파일 포함
include 'db_connect.php'; // $pdo 변수를 가져옴

// 3. 입력 데이터 받기 (GET 방식)
// (조회는 ?member_id=홍길동 처럼 GET 방식이 더 직관적입니다)
if (!isset($_GET['member_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => '조회할 member_id가 필요합니다.'
    ]);
    exit;
}
$member_id = $_GET['member_id'];

// 4. 최종 응답으로 보낼 빈 배열 생성
$response_data = [];

try {
    // 5. 쿼리 1: 회원의 기본 정보 (이름, 포인트) 조회
    $sql_member = "SELECT member_name, points FROM youth_members WHERE member_id = ?";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([$member_id]);
    $member_info = $stmt_member->fetch(); // 1개의 행만 가져옴

    if (!$member_info) {
        // 회원이 존재하지 않으면
        throw new Exception("해당 ID의 회원을 찾을 수 없습니다.");
    }

    // 응답 데이터에 기본 정보 추가
    $response_data['member_id'] = $member_id;
    $response_data['member_name'] = $member_info['member_name'];
    $response_data['points'] = $member_info['points'];

    // 6. 쿼리 2: 회원의 인벤토리 목록 조회 (JOIN 사용)
    // (youth_inventory에서 item_id를, youth_items에서 item_name을 가져옴)
    $sql_inventory = "SELECT T2.item_name, T1.quantity 
                      FROM youth_inventory AS T1
                      JOIN youth_items AS T2 ON T1.item_id = T2.item_id
                      WHERE T1.member_id = ?";
    
    $stmt_inventory = $pdo->prepare($sql_inventory);
    $stmt_inventory->execute([$member_id]);
    $inventory_list = $stmt_inventory->fetchAll(); // 모든 행을 배열로 가져옴

    // 응답 데이터에 인벤토리 목록 추가
    $response_data['inventory'] = $inventory_list;

    // 7. 최종 성공 응답
    echo json_encode([
        'status' => 'success',
        'data' => $response_data
    ]);

} catch (Exception $e) {
    // 8. 실패 응답
    echo json_encode([
        'status' => 'error',
        'message' => '조회 실패: ' . $e->getMessage(),
        'member_id' => $member_id // 어떤 ID가 실패했는지 알려줌
    ]);
}
?>