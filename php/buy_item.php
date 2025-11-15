<?php
/*
 * buy_item.php
 * 회원이 상점의 아이템을 구매합니다.
 */

// 1. CORS 헤더 설정 (★매우 중요★)
include 'auth_check.php'; 
// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// 2. DB 연결 파일 포함
include 'db_connect.php'; // $pdo 변수를 가져옴

// 3. 입력 데이터 받기 (POST 방식)
$input = json_decode(file_get_contents('php://input'), true);

// 4. 필수 값 확인
if (!isset($input['member_id']) || !isset($input['item_id'])) {
    echo json_encode(['status' => 'error', 'message' => '필수 값(member_id, item_id)이 누락되었습니다.']);
    exit;
}

// 5. 변수에 값 할당
$member_id = $input['member_id'];
$item_id = (int)$input['item_id'];
// 'quantity'는 선택사항. 없으면 1개로 간주
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1; 

if ($quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => '수량은 0보다 커야 합니다.']);
    exit;
}

// 6. DB 작업 (★트랜잭션★)
try {
    // 트랜잭션 시작
    $pdo->beginTransaction();

    // 쿼리 1: 아이템 정보 조회 (가격, 재고)
    // (데이터 변경 중 다른 사람이 수정 못하게 'FOR UPDATE'로 행을 잠급니다)
    $sql_item = "SELECT item_name, price, stock, status FROM youth_items WHERE item_id = ? FOR UPDATE";
    $stmt_item = $pdo->prepare($sql_item);
    $stmt_item->execute([$item_id]);
    $item = $stmt_item->fetch();

    if (!$item) {
        throw new Exception("존재하지 않는 아이템입니다.");
    }
    if ($item['status'] !== 'selling') {
        throw new Exception("판매중인 아이템이 아닙니다.");
    }

    // 쿼리 2: 재고 확인
    if ($item['stock'] != -1 && $item['stock'] < $quantity) { // -1은 무한 재고
        throw new Exception("아이템 재고가 부족합니다. (남은 재고: {$item['stock']}개)");
    }

    // 쿼리 3: 회원 정보 조회 (포인트)
    $sql_member = "SELECT points FROM youth_members WHERE member_id = ? FOR UPDATE";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([$member_id]);
    $member = $stmt_member->fetch();

    if (!$member) {
        throw new Exception("존재하지 않는 회원입니다.");
    }

    // 쿼리 4: 포인트 확인
    $total_price = $item['price'] * $quantity;
    if ($member['points'] < $total_price) {
        throw new Exception("포인트가 부족합니다. (보유: {$member['points']}P, 필요: {$total_price}P)");
    }

    // --- 모든 검증 통과! ---

    // 쿼리 5: 회원 포인트 차감
    $sql_update_member = "UPDATE youth_members SET points = points - ? WHERE member_id = ?";
    $pdo->prepare($sql_update_member)->execute([$total_price, $member_id]);

    // 쿼리 6: 인벤토리에 아이템 추가 (★핵심 쿼리★)
    // (이미 있으면 수량(quantity)을 더하고, 없으면 새로(INSERT) 만듦)
    $sql_inventory = "INSERT INTO youth_inventory (member_id, item_id, quantity)
                      VALUES (?, ?, ?)
                      ON DUPLICATE KEY UPDATE quantity = quantity + ?";
    $pdo->prepare($sql_inventory)->execute([$member_id, $item_id, $quantity, $quantity]);

    // 쿼리 7: 아이템 재고 차감 (-1이 아닐 경우에만)
    if ($item['stock'] != -1) {
        $sql_update_stock = "UPDATE youth_items SET stock = stock - ? WHERE item_id = ?";
        $pdo->prepare($sql_update_stock)->execute([$quantity, $item_id]);
    }

    // 쿼리 8: 포인트 로그 기록
    $reason = "{$item['item_name']} ({$quantity}개) 구매";
    $sql_log = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
    $pdo->prepare($sql_log)->execute([$member_id, -$total_price, $reason]);

    // 모든 작업 성공! DB에 최종 반영
    $pdo->commit();

    // 7. 성공 응답
    $message = "💬 [{$member_id}] 님이 [{$item['item_name']} x{$quantity}] 구매 완료! (-{$total_price}P)";
    echo json_encode([
        'status' => 'success',
        'message' => $message
    ]);

} catch (Exception $e) {
    // 8. 실패 응답 (어느 단계든 실패하면 모든 작업 롤백)
    $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => '구매 실패: ' . $e.getMessage()
    ]);
}
?>