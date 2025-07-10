<?php
// /pages/timeline_save.php (오류 처리 강화 버전)
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => '잘못된 요청 방식입니다.']);
    exit;
}

// CSRF 토큰 검증은 폼 데이터에 접근하기 전에 수행
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'CSRF 토큰 오류입니다.']);
    exit;
}

// 썸네일 파일 처리 로직 강화
$thumbnail_path = $_POST['existing_thumbnail'] ?? '';
if (isset($_FILES['thumbnail_file'])) {
    if ($_FILES['thumbnail_file']['error'] !== UPLOAD_ERR_NO_FILE) { // 파일이 첨부된 경우에만 처리
        $file = $_FILES['thumbnail_file'];
        
        // 오류 코드에 따른 상세 메시지 반환
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_message = '파일 업로드 중 오류가 발생했습니다.';
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message = '업로드한 파일이 서버에서 허용한 크기를 초과했습니다. (php.ini의 upload_max_filesize)';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message = '업로드한 파일이 HTML 폼에서 지정한 크기를 초과했습니다.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message = '파일이 부분적으로만 업로드되었습니다.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message = '임시 폴더가 없습니다.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message = '파일을 디스크에 쓸 수 없습니다.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message = 'PHP 확장 프로그램에 의해 파일 업로드가 중단되었습니다.';
                    break;
            }
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $error_message]);
            exit;
        }

        // 파일 업로드 처리
        $uploadDir = '../uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => '업로드 디렉터리 생성에 실패했습니다. 권한을 확인하세요.']);
                exit;
            }
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFileName = 'thumb_' . uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $thumbnail_path = 'uploads/thumbnails/' . $newFileName;
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
            exit;
        }
    }
}

// DB 저장 로직 (기존과 동일)
$id = intval($_POST['id'] ?? 0);
$year = $_POST['year'] ?? '-';
$desc = $_POST['full_description'] ?? '';

if ($id > 0) { // 수정
    $stmt = $mysqli->prepare("UPDATE eden_timeline SET year = ?, thumbnail = ?, full_description = ? WHERE id = ?");
    $stmt->bind_param("sssi", $year, $thumbnail_path, $desc, $id);
} else { // 새로 추가
    $max_order_res = $mysqli->query("SELECT MAX(sort_order) as max_order FROM eden_timeline");
    $new_order = ($max_order_res->fetch_assoc()['max_order'] ?? 0) + 10;
    $stmt = $mysqli->prepare("INSERT INTO eden_timeline (year, thumbnail, full_description, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $year, $thumbnail_path, $desc, $new_order);
}

if ($stmt->execute()) {
    $new_id = $id > 0 ? $id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'message' => '타임라인이 저장되었습니다.', 'redirect_url' => '#/timeline_detail?id=' . $new_id]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB 저장 실패: ' . $stmt->error]);
}
$stmt->close();
$mysqli->close();
?>