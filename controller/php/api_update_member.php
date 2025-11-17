<?php
/*
 * api_update_member.php
 * [SPA용 쓰기 API 4]
 * 기존 회원의 정보를 수정하고, '포인트 변경'이 있을 경우 로그에 기록합니다. (수정됨)
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
$new_member_name = $input['member_name'] ?? null;
$new_points = $input['points'] ?? null;

if (empty($member_id) || empty($new_member_name) || $new_points === null) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(member_id, member_name, points)이 누락되었습니다.';
} else {
    // 6. 로직: 회원 정보 업데이트 (★트랜잭션으로 변경★)
    try {
        // 트랜잭션 시작: 모든 작업은 한 묶음
        $pdo->beginTransaction();
        
        // 6-1. 현재 포인트 조회 (비교 및 수정을 위해 행 잠금)
        $sql_get = "SELECT points FROM youth_members WHERE member_id = ? FOR UPDATE";
        $stmt_get = $pdo->prepare($sql_get);
        $stmt_get->execute([$member_id]);
        $member = $stmt_get->fetch();
        
        if (!$member) {
            throw new Exception("존재하지 않는 회원입니다.");
        }
        
        $old_points = (int)$member['points'];
        $new_points_int = (int)$new_points;
        
        // 6-2. 포인트 변동량 계산
        // (예: 500 -> 600 수정 시, change = 100)
        // (예: 500 -> 400 수정 시, change = -100)
        $point_change = $new_points_int - $old_points;

        // 6-3. (★핵심★) 포인트에 변동이 있을 때만(0이 아닐 때) 로그 기록
        if ($point_change != 0) {
            $sql_log = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
            $stmt_log = $pdo->prepare($sql_log);
            $stmt_log->execute([$member_id, $point_change, "관리자 포인트 수정"]);
        }
        
        // 6-4. 회원 정보(이름, 포인트) 최종 업데이트
        $sql_update = "UPDATE youth_members 
                       SET member_name = ?, points = ? 
                       WHERE member_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$new_member_name, $new_points_int, $member_id]);
        
        // 6-5. 모든 작업이 성공했으므로 DB에 최종 반영
        $pdo->commit();
        
        $response['message'] = "회원 [{$new_member_name}] 님의 정보가 수정되었습니다.";
        
        // (보너스) 로그가 기록되었는지 응답 메시지에 추가
        if ($point_change != 0) {
             $response['message'] .= " (포인트 변동: {$point_change}P 로그 기록됨)";
        }

    } catch (Exception $e) {
        $pdo->rollBack(); // 오류 발생 시 모든 작업 되돌리기
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>