<?php
session_start();
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

// 1. 로그인 확인
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['status' => 'error', 'message' => '로그인이 필요합니다.']);
    exit;
}

// 2. 전송된 데이터 받기
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$thumbnail = $_POST['thumbnail'] ?? '';
$id = $_POST['id'] ?? null;

if (empty($title) || empty($content)) {
    echo json_encode(['status' => 'error', 'message' => '제목과 내용은 필수입니다.']);
    exit;
}

// 3. ▼▼▼ 썸네일 자동 추출 로직 (핵심) ▼▼▼
// 만약 썸네일이 비어있다면, 본문 내용에서 첫 번째 이미지 주소를 찾습니다.
if (empty($thumbnail)) {
    // 정규 표현식을 사용하여 HTML 내용에서 첫 번째 <img> 태그의 src 속성 값을 찾습니다.
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    
    // 만약 이미지를 찾았다면, 그 주소를 썸네일로 사용합니다.
    if (isset($matches[1])) {
        $thumbnail = $matches[1];
    }
}
// ▲▲▲ ================================= ▲▲▲

// 4. 데이터베이스에 저장 또는 수정
if ($id) { // 수정
    $stmt = $mysqli->prepare("UPDATE gallery SET title = ?, content = ?, thumbnail = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $content, $thumbnail, $id);
} else { // 신규 작성
    $stmt = $mysqli->prepare("INSERT INTO gallery (title, content, thumbnail) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $thumbnail);
}

if ($stmt->execute()) {
    // 성공 시, 새로 생성된 게시글의 ID를 함께 보내주면 더 좋습니다. (수정 시에는 기존 ID)
    $new_id = $id ? $id : $mysqli->insert_id; 
    echo json_encode(['status' => 'success', 'id' => $new_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => '데이터베이스 저장에 실패했습니다.']);
}

$stmt->close();
$mysqli->close();
?>