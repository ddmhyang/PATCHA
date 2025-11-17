<?php
/*
 * api_transfer_item.php
 * [SPA용 쓰기 API 11]
 * 한 회원의 아이템을 다른 회원에게 양도합니다. (트랜잭션)
 * (★ 아이템 로그, 응답 메시지에 '이름'이 표시되도록 수정됨)
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
$item_id = (int)($input['item_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);

if (empty($sender_id) || empty($receiver_id) || $item_id <= 0 || $quantity <= 0) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(보내는 분, 받는 분, 아이템, 수량)이 잘못되었습니다.';
    echo json_encode($response);
    exit;
}

if ($sender_id === $receiver_id) {
    $response['status'] = 'error';
    $response['message'] = '스스로에게 양도할 수 없습니다.';
    echo json_encode($response);
    exit;
}

// 6. 로직: 아이템 양도 (★트랜잭션★)
try {
    $pdo->beginTransaction();

    // 6-1. 보내는 사람(Sender) 인벤토리 조회 및 잠금
    $sql_sender = "SELECT quantity FROM youth_inventory WHERE member_id = ? AND item_id = ? FOR UPDATE";
    $stmt_sender = $pdo->prepare($sql_sender);
    $stmt_sender->execute([$sender_id, $item_id]);
    $sender_item = $stmt_sender->fetch();

    if (!$sender_item) {
        throw new Exception("보내는 분({$sender_id})이 해당 아이템(ID: {$item_id})을 가지고 있지 않습니다.");
    }
    
    // 6-2. 수량 확인
    if ($sender_item['quantity'] < $quantity) {
        throw new Exception("보내는 분의 아이템 수량이 부족합니다. (보유: {$sender_item['quantity']}개)");
    }
    
    // ★★★★★★★ (신규) 6-2b. 보내는 사람 이름 조회 ★★★★★★★
    $sql_sender_name = "SELECT member_name FROM youth_members WHERE member_id = ?";
    $stmt_sender_name = $pdo->prepare($sql_sender_name);
    $stmt_sender_name->execute([$sender_id]);
    $sender_name = $stmt_sender_name->fetchColumn(); // 이름만 가져오기
    if (!$sender_name) {
        throw new Exception("보내는 분({$sender_id})의 회원 정보를 찾을 수 없습니다.");
    }

    // ★★★★★★★ (신규) 6-2c. 받는 사람 이름 조회 ★★★★★★★
    $sql_receiver_name = "SELECT member_name FROM youth_members WHERE member_id = ?";
    $stmt_receiver_name = $pdo->prepare($sql_receiver_name);
    $stmt_receiver_name->execute([$receiver_id]);
    $receiver_name = $stmt_receiver_name->fetchColumn(); // 이름만 가져오기
    if (!$receiver_name) {
        // (받는 사람이 DB에 없으면 Foreign Key 제약조건으로 어차피 실패하므로 미리 확인)
        throw new Exception("받는 분({$receiver_id})의 회원 정보를 찾을 수 없습니다.");
    }

    // 6-3. 보내는 사람 아이템 차감
    if ($sender_item['quantity'] == $quantity) {
        // (행 삭제)
        $sql_update_sender = "DELETE FROM youth_inventory WHERE member_id = ? AND item_id = ?";
        $pdo->prepare($sql_update_sender)->execute([$sender_id, $item_id]);
    } else {
        // (수량 업데이트)
        $sql_update_sender = "UPDATE youth_inventory SET quantity = quantity - ? WHERE member_id = ? AND item_id = ?";
        $pdo->prepare($sql_update_sender)->execute([$quantity, $sender_id, $item_id]);
    }
    
    // 6-4. 받는 사람 아이템 증가
    $sql_update_receiver = "INSERT INTO youth_inventory (member_id, item_id, quantity)
                            VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE quantity = quantity + ?";
    $pdo->prepare($sql_update_receiver)->execute([$receiver_id, $item_id, $quantity, $quantity]);
    
    // 6-5. (★수정★) 보내는 사람(-수량) 로그 기록
    $reason_sender = "{$receiver_name}({$receiver_id})님에게 양도"; // (이름으로 변경)
    $sql_log_sender = "INSERT INTO youth_item_logs (member_id, item_id, quantity_change, reason) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql_log_sender)->execute([$sender_id, $item_id, -$quantity, $reason_sender]);
    
    // 6-6. (★수정★) 받는 사람(+수량) 로그 기록
    $reason_receiver = "{$sender_name}({$sender_id})님으로부터 받음"; // (이름으로 변경)
    $sql_log_receiver = "INSERT INTO youth_item_logs (member_id, item_id, quantity_change, reason) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql_log_receiver)->execute([$receiver_id, $item_id, $quantity, $reason_receiver]);
    
    // 6-7. 모든 작업 성공! DB 최종 반영
    $pdo->commit();

    // 6-8. (★수정★) 최종 응답 메시지 (이름으로 변경)
    $response['message'] = "💬 [{$sender_name}] 님이 [{$receiver_name}] 님에게 [아이템 ID: {$item_id}] {$quantity}개 양도 완료.";

} catch (Exception $e) {
    $pdo->rollBack(); // 오류 발생 시 모든 작업 되돌리기
    $response['status'] = 'error';
    $response['message'] = "아이템 양도 실패: " . $e->getMessage();
}

// 7. 최종 JSON 응답
echo json_encode($response);
?>