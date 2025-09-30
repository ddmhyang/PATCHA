<?php
// ---- 재사용할 데이터 저장 PHP 코드 ----

// 응답 헤더를 JSON으로 설정
header('Content-Type: application/json');

// DB 연결
require_once '../includes/db.php';

// (보안) 관리자만 접근 가능하도록 설정
if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// 1. JS로부터 받은 데이터(POST)를 변수에 저장
$page_key = $_POST['page_key'] ?? '';
$content = $_POST['content'] ?? '';

// 2. 필수 데이터가 있는지 확인
if (empty($page_key) || empty($content)) {
    echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    exit;
}

// 3. SQL Injection 공격 방어를 위해 Prepared Statement 사용
$stmt = $mysqli->prepare("UPDATE pages SET content = ? WHERE page_key = ?");
// s: string(문자열), 첫번째 ?에 $content, 두번째 ?에 $page_key를 바인딩
$stmt->bind_param("ss", $content, $page_key);

// 4. 쿼리 실행 및 결과에 따른 JSON 응답 전송
if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => '성공적으로 저장되었습니다.',
        'redirect_url' => '#/' . $page_key // 저장 후 이동할 페이지
    ]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장에 실패했습니다.']);
}

$stmt->close();
?>