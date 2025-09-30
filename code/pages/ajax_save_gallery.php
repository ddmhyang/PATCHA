<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// 폼에서 전송된 모든 데이터를 받습니다. id가 없으면 0으로 설정되어 '새 글'로 인식됩니다.
$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'];
$content = $_POST['content'];
$gallery_type = $_POST['gallery_type'];
$is_private = isset($_POST['is_private']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$thumbnail_path = null;

// 1. 썸네일 파일이 직접 업로드되었는지 확인
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['thumbnail'];
    $uploadDir = '../uploads/gallery/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    // pathinfo()로 파일 확장자를 가져오고, uniqid()로 고유한 파일 이름을 만듭니다.
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $newFileName = 'thumb-' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;
    // 파일을 지정된 경로로 이동시키고 성공하면, $thumbnail_path 변수에 웹 경로를 저장합니다.
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $thumbnail_path = '/uploads/gallery/' . $newFileName;
    }
}

// 2. 만약 썸네일이 직접 업로드되지 않았다면, 본문(content)에서 첫 번째 이미지 태그를 찾습니다.
if (empty($thumbnail_path)) {
    // 정규표현식을 사용해 <img ... src="..." ...> 형태에서 src 안의 주소 값을 추출합니다.
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    // 추출에 성공하면, 그 주소를 썸네일 경로로 사용합니다.
    if (isset($matches[1])) {
        $thumbnail_path = $matches[1];
    }
}

// 3. 비밀글이고 비밀번호가 입력되었다면, 비밀번호를 password_hash() 함수로 안전하게 암호화합니다.
$password_hash = null;
if ($is_private && !empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

// 4. 수정 모드($post_id > 0)인지, 새 글 작성 모드인지에 따라 다른 SQL 쿼리를 구성합니다.
if ($post_id > 0) { // 수정
    // 기본적으로 title, content, is_private를 업데이트하는 쿼리를 준비합니다.
    $sql = "UPDATE chan_gallery SET title=?, content=?, is_private=?";
    $params = [$title, $content, $is_private];
    $types = "ssi"; // bind_param에 사용할 타입 문자열 (string, string, integer)
    // 썸네일이나 비밀번호가 변경되었을 경우에만 쿼리에 해당 부분을 추가합니다.
    if ($thumbnail_path) { $sql .= ", thumbnail=?"; $params[] = $thumbnail_path; $types .= "s"; }
    if ($password_hash) { $sql .= ", password_hash=?"; $params[] = $password_hash; $types .= "s"; }
    $sql .= " WHERE id=?"; // 마지막으로 어떤 id를 수정할지 지정합니다.
    $params[] = $post_id;
    $types .= "i";
    $stmt = $mysqli->prepare($sql);
    // ...$params (스프레드 연산자)를 사용해 $params 배열의 모든 요소를 bind_param의 인자로 전달합니다.
    $stmt->bind_param($types, ...$params);
} else { // 새 글
    // 모든 컬럼에 값을 INSERT하는 쿼리를 준비합니다.
    $stmt = $mysqli->prepare("INSERT INTO chan_gallery (gallery_type, title, content, thumbnail, is_private, password_hash) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $gallery_type, $title, $content, $thumbnail_path, $is_private, $password_hash);
}

// 5. 준비된 쿼리를 실행하고 결과를 응답합니다.
if ($stmt->execute()) {
    // 수정이면 기존 id, 새 글이면 방금 생성된 id($mysqli->insert_id)를 가져옵니다.
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    // 성공 시, 방금 저장한 게시물의 상세 보기 페이지로 이동하라는 redirect_url을 포함하여 응답합니다.
    echo json_encode(['success' => true, 'redirect_url' => "#/gallery_view?id=" . $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
$stmt->close();
?>