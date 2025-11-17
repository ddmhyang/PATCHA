<?php
/*
 * api_get_all_members.php
 * [SPA용 읽기 API 1]
 * 모든 회원 목록을 JSON으로 반환합니다.
 */

// 1. CORS 헤더 설정 (★매우 중요★)
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
    // 3. 로직: 전체 회원 목록 조회 (포인트 높은 순)
    $sql = "SELECT member_id, member_name, points FROM youth_members ORDER BY points DESC";
    $stmt = $pdo->query($sql);
    $members_list = $stmt->fetchAll();
    
    $response['data'] = $members_list;

} catch (Exception $e) {
    // 4. 실패 응답
    $response['status'] = 'error';
    $response['message'] = '회원 목록 조회 실패: ' . $e->getMessage();
}

// 5. 최종 JSON 응답
echo json_encode($response);
?>