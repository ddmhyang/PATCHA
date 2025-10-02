<?php
// ... (상단 코드 생략) ...

// 1. JS로부터 받은 데이터(POST)를 변수에 저장 (page_key -> slug 로 수정)
$slug = $_POST['slug'] ?? ''; // 이 부분을 수정하세요
$content = $_POST['content'] ?? '';

// 2. 필수 데이터가 있는지 확인 (page_key -> slug 로 수정)
if (empty($slug) || empty($content)) { // 이 부분을 수정하세요
    echo json_encode(['success' => false, 'message' => '필수 데이터가 누락되었습니다.']);
    exit;
}

// 3. SQL Injection 공격 방어를 위해 Prepared Statement 사용 (page_key -> slug 로 수정)
$stmt = $mysqli->prepare("UPDATE pages SET content = ? WHERE slug = ?"); // 이 부분을 수정하세요
// s: string(문자열), 첫번째 ?에 $content, 두번째 ?에 $slug를 바인딩
$stmt->bind_param("ss", $content, $slug); // 이 부분을 수정하세요

// 4. 쿼리 실행 및 결과에 따른 JSON 응답 전송 (page_key -> slug 로 수정)
if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => '성공적으로 저장되었습니다.',
        'redirect_url' => '#/' . $slug // 이 부분을 수정하세요
    ]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 저장에 실패했습니다.']);
}

$stmt->close();
?>