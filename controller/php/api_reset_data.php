<?php
/*
 * api_reset_data.php
 * [SPA용 쓰기 API - 위험!]
 * 관리자 계정, 상점, 도박을 제외한 '모든 운영 데이터'를 삭제(초기화)합니다.
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

$response = ['status' => 'success'];

// 4. 로직: (★트랜잭션으로 묶음★)
try {
    $pdo->beginTransaction();

    // (★중요★) SQLite는 외래 키(FK)가 켜져 있어도
    // DELETE FROM (TRUNCATE)을 막지 않습니다.
    // 외래 키 제약 조건(FK)을 잠시 끕니다.
    $pdo->exec('PRAGMA foreign_keys = OFF;');

    // 1. 회원 목록 삭제
    $pdo->exec("DELETE FROM youth_members;");
    
    // 2. 인벤토리 삭제
    $pdo->exec("DELETE FROM youth_inventory;");
    
    // 3. 포인트 로그 삭제
    $pdo->exec("DELETE FROM youth_point_logs;");
    
    // 4. 아이템 로그 삭제
    $pdo->exec("DELETE FROM youth_item_logs;");
    
    // (SQLite는 테이블을 비워도 AUTOINCREMENT 값이 초기화되지 않음 - 별도 처리)
    $pdo->exec("DELETE FROM sqlite_sequence WHERE name IN (
        'youth_point_logs', 
        'youth_item_logs'
    );");
    
    // 외래 키 제약 조건을 다시 켭니다.
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // 5. 모든 작업 성공! DB 최종 반영
    $pdo->commit();

    $response['message'] = "✅ 데이터 초기화 성공: 모든 회원, 인벤토리, 포인트/아이템 로그가 삭제되었습니다.";

} catch (Exception $e) {
    $pdo->rollBack(); // 오류 발생 시 모든 작업 되돌리기
    // 만약 롤백했다면 외래 키를 다시 켜줍니다.
    $pdo->exec('PRAGMA foreign_keys = ON;');
    
    $response['status'] = 'error';
    $response['message'] = "초기화 실패: " . $e->getMessage();
}

// 6. 최종 JSON 응답
echo json_encode($response);
?>