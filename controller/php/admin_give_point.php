<?php
// ★★★ 1순위: 로그인 안 했으면 여기서 즉시 차단! ★★★
include 'auth_check.php'; 

// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 

// 3. CORS 헤더 설정
header("Access-Control-Allow-Origin: *"); // 실제 서비스 시엔 '*' 대신 확장 프로그램 ID를 쓰기도 합니다.
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8"); // 항상 JSON으로 응답

// 2. DB 연결 파일 포함
include 'db_connect.php'; // $pdo 변수를 가져옴

// 3. 입력 데이터 받기 (POST 방식)
// 확장 프로그램이 body에 JSON으로 담아 보낸다고 가정합니다.
$input = json_decode(file_get_contents('php://input'), true);

// 4. 입력 값 확인
if (!isset($input['member_id']) || !isset($input['points']) || !isset($input['reason'])) {
    // 필수 값이 없으면 에러 메시지 반환
    echo json_encode([
        'status' => 'error',
        'message' => '필수 입력 값(member_id, points, reason)이 누락되었습니다.'
    ]);
    exit; // 스크립트 종료
}

// 5. 변수에 값 할당
$member_id = $input['member_id'];
$points_change = (int)$input['points']; // 정수로 변환
$reason = $input['reason'];

// 6. DB 작업 (★트랜잭션★)
try {
    // 트랜잭션 시작: "지금부터 모든 작업은 한 묶음이다!"
    $pdo->beginTransaction();

    // 쿼리 1: youth_members 테이블의 포인트 업데이트
    // (SQL Injection을 방지하기 위해 prepared statement '?'를 사용합니다)
    $sql_update = "UPDATE youth_members SET points = points + ? WHERE member_id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$points_change, $member_id]);

    // 쿼리 2: youth_point_logs 테이블에 내역 기록
    $sql_log = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
    $stmt_log = $pdo->prepare($sql_log);
    $stmt_log->execute([$member_id, $points_change, $reason]);

    // 모든 쿼리가 성공하면 DB에 최종 반영
    $pdo->commit();

    // 7. 성공 응답 (확장 프로그램이 받을 메시지)
    $action = ($points_change >= 0) ? "지급" : "회수";
    $message = "💬 [{$member_id}] 님에게 {$points_change}P를 [{$reason}] 사유로 {$action}했습니다.";
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'member_id' => $member_id,
        'points_change' => $points_change
    ]);

} catch (Exception $e) {
    // 쿼리 실행 중 하나라도 실패하면 모든 작업을 취소 (롤백)
    $pdo->rollBack();

    // 8. 실패 응답
    echo json_encode([
        'status' => 'error',
        'message' => 'DB 작업 실패: ' . $e->getMessage()
    ]);
}
?>