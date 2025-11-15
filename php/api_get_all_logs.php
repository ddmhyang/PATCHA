<?php
/*
 * api_get_all_logs.php
 * [SPA용 읽기 API 4]
 * 모든 포인트 변동 로그를 JSON으로 반환합니다.
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
    // 3. 로직: 전체 로그 조회 (LEFT JOIN 사용)
    // (회원이 삭제되어도 로그는 남도록)
    $sql = "SELECT T1.log_id, T1.log_time, T1.member_id, T1.point_change, T1.reason, T2.member_name
            FROM youth_point_logs AS T1
            LEFT JOIN youth_members AS T2 ON T1.member_id = T2.member_id
            ORDER BY T1.log_time DESC"; // 최근순
            
    $stmt = $pdo->query($sql);
    $logs_list = $stmt->fetchAll();
    
    $response['data'] = $logs_list;

} catch (Exception $e) {
    // 4. 실패 응답
    $response['status'] = 'error';
    $response['message'] = '로그 조회 실패: ' . $e->getMessage();
}

// 5. 최종 JSON 응답
echo json_encode($response);
?>