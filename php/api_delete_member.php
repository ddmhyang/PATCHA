<?php
/*
 * api_delete_member.php
 * [SPA용 쓰기 API 5]
 * 회원을 삭제합니다.
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
$member_id = $input['member_id'] ?? null;

if (empty($member_id)) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(member_id)이 누락되었습니다.';
} else {
    // 6. 로직: 회원 삭제
    try {
        $sql = "DELETE FROM youth_members WHERE member_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$member_id]);
        
        // SQL 파일의 Cascade/Set Null 설정으로
        // inventory, point_logs가 자동 처리됨.

        $response['message'] = "회원 [{$member_id}] 님이 삭제되었습니다.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>