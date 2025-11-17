<?php
/*
 * run_gamble.php (★ SQLite 완전 호환 버전)
 */

// ★★★ 1순위: 로그인 인증 ★★★
include 'auth_check.php';
// 2. DB 연결
include 'db_connect.php'; 

// ... (CORS, 입력 값 확인 부분은 동일) ...
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['member_id']) || !isset($input['game_id']) || !isset($input['bet_amount'])) { /* ... 오류 ... */ exit; }
$member_id = $input['member_id'];
$game_id = (int)$input['game_id'];
$bet_amount = (int)$input['bet_amount'];
if ($bet_amount <= 0) { /* ... 오류 ... */ exit; }

// 6. DB 작업 (★트랜잭션★)
try {
    $pdo->beginTransaction();

    // 쿼리 1: 회원 정보 조회 (★ FOR UPDATE 제거)
    $sql_member = "SELECT points FROM youth_members WHERE member_id = ?";
    $stmt_member = $pdo->prepare($sql_member);
    $stmt_member->execute([$member_id]);
    $member = $stmt_member->fetch();

    if (!$member) { throw new Exception("존재하지 않는 회원입니다."); }
    if ($member['points'] < $bet_amount) { throw new Exception("베팅 금액보다 보유 포인트가 적습니다."); }

    // 쿼리 2: 도박 게임 규칙 조회 (동일)
    $sql_game = "SELECT game_name, outcomes FROM youth_gambling_games WHERE game_id = ?";
    $stmt_game = $pdo->prepare($sql_game);
    $stmt_game->execute([$game_id]);
    $game = $stmt_game->fetch();
    if (!$game) { throw new Exception("존재하지 않는 게임입니다."); }

    // 쿼리 3: 베팅 금액 차감 (동일)
    $sql_bet = "UPDATE youth_members SET points = points - ? WHERE member_id = ?";
    $pdo->prepare($sql_bet)->execute([$bet_amount, $member_id]);

    // 쿼리 4: 베팅 로그 기록 (동일)
    $reason_bet = "{$game['game_name']} 베팅 (-{$bet_amount}P)";
    $sql_log_bet = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
    $pdo->prepare($sql_log_bet)->execute([$member_id, -$bet_amount, $reason_bet]);

    // ... (이하 도박 로직 및 로그 기록 부분은 모두 동일) ...
    $outcomes_array = explode(',', $game['outcomes']);
    $random_key = array_rand($outcomes_array);
    $multiplier = (float)$outcomes_array[$random_key];
    $winnings = $bet_amount * $multiplier;
    $point_change = $winnings;
    $message = "";

    if ($point_change > 0) {
        $sql_win = "UPDATE youth_members SET points = points + ? WHERE member_id = ?";
        $pdo->prepare($sql_win)->execute([$point_change, $member_id]);
        $reason_win = "{$game['game_name']} 당첨! ({$multiplier}배)";
        $sql_log_win = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
        $pdo->prepare($sql_log_win)->execute([$member_id, $point_change, $reason_win]);
        $message = "💬 잭팟! [{$member_id}] 님이 [{$game['game_name']}]({$multiplier}배)로 {$point_change}P 획득!";
    
    } else if ($point_change < 0) {
        $sql_lose = "UPDATE youth_members SET points = points + ? WHERE member_id = ?";
        $pdo->prepare($sql_lose)->execute([$point_change, $member_id]);
        $reason_lose = "{$game['game_name']} 파산! ({$multiplier}배)";
        $sql_log_lose = "INSERT INTO youth_point_logs (member_id, point_change, reason) VALUES (?, ?, ?)";
        $pdo->prepare($sql_log_lose)->execute([$member_id, $point_change, $reason_lose]);
        $message = "💬 꽝! [{$member_id}] 님이 [{$game['game_name']}]({$multiplier}배)로 {$point_change}P 손해...";
    } else {
        $message = "💬 본전... [{$member_id}] 님이 [{$game['game_name']}]({$multiplier}배)로 변동 없습니다.";
    }

    $pdo->commit();

    // 9. 최종 성공 응답
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'multiplier' => $multiplier,
        'winnings' => $point_change
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([ 'status' => 'error', 'message' => '도박 실패: ' . $e->getMessage() ]);
}
?>