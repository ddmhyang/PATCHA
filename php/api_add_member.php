<?php
/*
 * api_add_member.php
 * [SPA용 쓰기 API 1]
 * 새 회원을 등록합니다.
 */

// 1. CORS 헤더 설정
include 'auth_check.php'; 
// 2. DB 연결 (로그인 성공한 사람만 DB 연결)
include 'db_connect.php'; 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // '쓰기'는 POST 방식
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// 2. DB 연결
include 'db_connect.php'; // $pdo

// 3. 입력 데이터 받기 (POST 방식)
$input = json_decode(file_get_contents('php://input'), true);

$response = ['status' => 'success']; // 기본 성공 응답

// 4. 입력 값 확인
if (!isset($input['member_id']) || !isset($input['member_name']) || 
    empty($input['member_id']) || empty($input['member_name'])) {
    
    $response['status'] = 'error';
    $response['message'] = '필수 값(member_id, member_name)이 누락되었습니다.';

} else {
    // 5. 로직: 새 회원 등록
    try {
        $sql = "INSERT INTO youth_members (member_id, member_name, points) VALUES (?, ?, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input['member_id'], $input['member_name']]);
        
        $response['message'] = "회원 [{$input['member_name']}] 님이 등록되었습니다.";

    } catch (PDOException $e) {
        $response['status'] = 'error';
        // member_id가 PK라서 중복되면 에러 (코드 23000)
        if ($e->getCode() == 23000) {
            $response['message'] = "이미 존재하는 회원 ID입니다.";
        } else {
            $response['message'] = "DB 오류: " . $e->getMessage();
        }
    }
}

// 6. 최종 JSON 응답
echo json_encode($response);
?>