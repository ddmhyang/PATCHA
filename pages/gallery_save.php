<?php
// /pages/gallery_save.php
require_once '../includes/db.php';

// 1. 관리자 권한 확인
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    die("권한이 없습니다.");
}

// 2. POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3. CSRF 토큰 검증
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF 토큰이 유효하지 않습니다. 폼을 다시 제출해주세요.');
    }

    // 4. 폼 데이터 가져오기
    $title = $_POST['title'];
    $content = $_POST['content'];
    $gallery_type = $_POST['gallery_type'];
    $post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    // 5. 데이터베이스 작업 (수정 또는 생성)
    if ($post_id > 0) { // 수정 모드
        $stmt = $mysqli->prepare("UPDATE eden_gallery SET title = ?, content = ?, gallery_type = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $content, $gallery_type, $post_id);
    } else { // 생성 모드
        $stmt = $mysqli->prepare("INSERT INTO eden_gallery (title, content, gallery_type) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $gallery_type);
    }

    // 6. 실행 및 결과에 따른 리디렉션
    if ($stmt->execute()) {
        $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
        header("Location: ../main.php?page=gallery_view&id=" . $new_id);
        exit;
    } else {
        die("저장에 실패했습니다: " . $stmt->error);
    }
    $stmt->close();
}
$mysqli->close();
?>