<?php
/*
 * run_gamble.php
 * 회원이 도박 게임을 실행합니다.
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
if (!isset($input['member_id']) || !isset($input['game_id']) || !isset($input['bet_amount'])) {
    echo json_encode(['status' => 'error', 'message' => '필수 값(member_id, game_id, bet_amount)이 누락되었습니다.']);
    exit;
}

// 5. 변수에 값 할당
$member_id = $input['member_id'];
$game_id = (int)$input['game_id'];
$bet_amount = (int)$input['bet_amount'];

if ($bet_amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => '베팅 금액은 0보다 커야 합니다.']);
    exit;
}

// 6. DB 작업 (★트랜잭션★)
try {
    // 트랜잭션 시작
    $pdo->beginTransaction();

    // 쿼리 1: 회원 정보 조회 (포인트 확인)
    $sql_member = "SELECT points FROM youth_members WHERE member_id = ? FOR UPDATE";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([$member_id]);
    $member = $stmt_member->fetch();

    if (!$member) {
        throw new Exception("존재하지 않는 회원입니다.");
    }
    if ($member['points'] < $bet_amount) {
        throw new Exception("베팅 금액보다 보유 포인트가 적습니다. (보유: {$member['points']}P)");
    }

    // 쿼리 2: 도박 게임 규칙 조회
    $sql_game = "SELECT game_name, outcomes FROM youth_gambling_games WHERE game_id = ?";
    $stmt_game = $pdo->prepare($sql_game);
    $stmt_game->execute([$game_id]);
    $game = $stmt_game->fetch();

    if (!$game) {
        throw new Exception("존재하지 않는 게임입니다.");
    }

    // 쿼리 3: 베팅 금액 차감 (먼저 100% 나가는 돈부터 처리)
    $sql_bet = "UPDATE youth_members SET points = points - ? WHERE member_id = ?";
    $pdo->prepare($sql_bet)->execute([$bet_amount, $member_id]);

    // 쿼리 4: 베팅 로그 기록
    $reason_bet = "{$game['game_name']} 베팅 (-{$bet_amount}P)";
    $sql_log_bet = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
    $pdo->prepare($sql_log_bet)->execute([$member_id, -$bet_amount, $reason_bet]);

    // --- 여기가 진짜 도박 로직 ---
    
    // 5. 배율(outcomes) 목록을 배열로 변환
    // (예: "-10,-5,0,1,5,10")
    $outcomes_array = explode(',', $game['outcomes']);
    
    // 6. 배열에서 무작위로 하나 추첨
    $random_key = array_rand($outcomes_array);
    $multiplier = (float)$outcomes_array[$random_key]; // 소수점 배율도 가능하게 float으로

    // 7. 당첨금(winnings) 계산
    // (예: 100 * 5배 = 500 / 100 * 0배 = 0 / 100 * -10배 = -1000)
    $winnings = $bet_amount * $multiplier;

    // 8. 당첨금/손해금 처리
    $point_change = $winnings;
    $message = "";

    if ($point_change > 0) {
        // 쿼리 8a: 당첨금 지급
        $sql_win = "UPDATE youth_members SET points = points + ? WHERE member_id = ?";
        $pdo->prepare($sql_win)->execute([$point_change, $member_id]);
        
        // 쿼리 8b: 당첨 로그
        $reason_win = "{$game['game_name']} 당첨! ({$multiplier}배)";
        $sql_log_win = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
        $pdo->prepare($sql_log_win)->execute([$member_id, $point_change, $reason_win]);
        
        $message = "💬 잭팟! [{$member_id}] 님이 [{$game['game_name']}]({$multiplier}배)로 {$point_change}P 획득!";
    
    } else if ($point_change < 0) {
        // 쿼리 8a: 손해금 차감 (예: -10배)
        $sql_lose = "UPDATE youth_members SET points = points + ? WHERE member_id = ?"; // (+ (-1000))
        $pdo->prepare($sql_lose)->execute([$point_change, $member_id]); // $point_change 자체가 음수
        
        // 쿼리 8b: 손해 로그
        $reason_lose = "{$game['game_name']} 파산! ({$multiplier}배)";
        $sql_log_lose = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
        $pdo->prepare($sql_log_lose)->execute([$member_id, $point_change, $reason_lose]);

        $message = "💬 꽝! [{$member_id}] 님이 [{$game['game_name']}]({$multiplier}배)로 {$point_change}P 손해...";

    } else { // 0배 (본전)
        $message = "💬 본전... [{$member_id}] 님이 [{$game['game_name']}]({$multiplier}배)로 변동 없습니다.";
    }

    // 모든 작업 성공! DB에 최종 반영
    $pdo->commit();

    // 9. 최종 성공 응답
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'multiplier' => $multiplier,
        'winnings' => $point_change
    ]);

} catch (Exception $e) {
    // 10. 실패 응답 (어느 단계든 실패하면 모든 작업 롤백)
    $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => '도박 실패: ' . $e->getMessage()
    ]);
}
?>