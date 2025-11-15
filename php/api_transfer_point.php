<?php
/*
 * api_transfer_points.php
 * [SPA/조종기용 쓰기 API 8]
 * 한 회원의 포인트를 다른 회원에게 양도합니다. (트랜잭션)
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
$sender_id = $input['sender_id'] ?? null;
$receiver_id = $input['receiver_id'] ?? null;
$amount = (int)($input['amount'] ?? 0);

if (empty($sender_id) || empty($receiver_id) || $amount <= 0) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(보내는 분, 받는 분)이 없거나, 금액이 0 이하입니다.';
    echo json_encode($response);
    exit;
}

if ($sender_id === $receiver_id) {
    $response['status'] = 'error';
    $response['message'] = '스스로에게 양도할 수 없습니다.';
    echo json_encode($response);
    exit;
}

// 6. 로직: 포인트 양도 (★트랜잭션★)
try {
    $pdo->beginTransaction();

    // 6-1. 보내는 사람(Sender) 포인트 조회 및 잠금
    $sql_sender = "SELECT points, member_name FROM youth_members WHERE member_id = ? FOR UPDATE";
    $stmt_sender = $pdo->prepare($sql_sender);
    $stmt_sender->execute([$sender_id]);
    $sender = $stmt_sender->fetch();

    if (!$sender) {
        throw new Exception("보내는 분({$sender_id})을 찾을 수 없습니다.");
    }
    
    // 6-2. 잔액 확인
    if ($sender['points'] < $amount) {
        throw new Exception("보내는 분의 포인트가 부족합니다. (보유: {$sender['points']}P)");
    }
    
    // 6-3. 받는 사람(Receiver) 이름 조회 (로그용)
    $sql_receiver = "SELECT member_name FROM youth_members WHERE member_id = ?";
    $stmt_receiver = $pdo->prepare($sql_receiver);
    $stmt_receiver->execute([$receiver_id]);
    $receiver = $stmt_receiver->fetch();
    
    if (!$receiver) {
        throw new Exception("받는 분({$receiver_id})을 찾을 수 없습니다.");
    }

    $sender_name = $sender['member_name'];
    $receiver_name = $receiver['member_name'];

    // 6-4. 보내는 사람 포인트 차감
    $sql_update_sender = "UPDATE youth_members SET points = points - ? WHERE member_id = ?";
    $pdo->prepare($sql_update_sender)->execute([$amount, $sender_id]);
    
    // 6-5. 받는 사람 포인트 증가
    $sql_update_receiver = "UPDATE youth_members SET points = points + ? WHERE member_id = ?";
    $pdo->prepare($sql_update_receiver)->execute([$amount, $receiver_id]);
    
    // 6-6. 보내는 사람 로그 기록
    $reason_sender = "{$receiver_name}({$receiver_id})님에게 양도";
    $sql_log_sender = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
    $pdo->prepare($sql_log_sender)->execute([$sender_id, -$amount, $reason_sender]);
    
    // 6-7. 받는 사람 로그 기록
    $reason_receiver = "{$sender_name}({$sender_id})님으로부터 받음";
    $sql_log_receiver = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
    $pdo->prepare($sql_log_receiver)->execute([$receiver_id, $amount, $reason_receiver]);

    // 6-8. 모든 작업 성공! DB 최종 반영
    $pdo->commit();

    $response['message'] = "💬 [{$sender_name}] 님이 [{$receiver_name}] 님에게 {$amount}P 양도 완료.";

} catch (Exception $e) {
    $pdo->rollBack(); // 오류 발생 시 모든 작업 되돌리기
    $response['status'] = 'error';
    $response['message'] = "양도 실패: " . $e->getMessage();
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>