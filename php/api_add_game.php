<?php
/*
 * api_add_game.php
 * [SPA용 쓰기 API 3]
 * 새 도박 게임을 등록합니다.
 */

// 1. CORS 헤더 설정
// ★★★ 1순위: 로그인 안 했으면 여기서 즉시 차단! ★★★
include 'auth_check.php'; 
// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // '쓰기'는 POST
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// 2. DB 연결
include 'db_connect.php'; // $pdo

// 3. 입력 데이터 받기 (POST 방식)
$input = json_decode(file_get_contents('php://input'), true);

$response = ['status' => 'success']; // 기본 성공 응답

// 4. 입력 값 확인
$game_name = $input['game_name'] ?? '';
$description = $input['description'] ?? '';
$outcomes = $input['outcomes'] ?? '';

// outcomes 유효성 검사 (예: "-10,-5,1,10")
if (empty($game_name) || empty($outcomes)) {
    $response['status'] = 'error';
    $response['message'] = '필수 값(게임 이름, 배율 목록)이 누락되었습니다.';

} elseif (!preg_match('/^([-+]?[0-9]*\.?[0-9]+,?)+$/', $outcomes)) {
    // (보안 및 API 안정성을 위해 유효성 검사)
    $response['status'] = 'error';
    $response['message'] = '배율 목록 형식이 잘못되었습니다. 숫자와 쉼표(,)만 사용하세요. (예: -10,-5,1,10)';

} else {
    // 5. 로직: 새 도박 게임 등록
    try {
        $sql = "INSERT INTO youth_gambling_games (game_name, description, outcomes) 
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$game_name, $description, $outcomes]);
        
        $response['message'] = "도박 게임 [{$game_name}] 이(가) 등록되었습니다.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = "DB 오류: " . $e->getMessage();
    }
}

// 6. 최종 JSON 응답
echo json_encode($response);
?>