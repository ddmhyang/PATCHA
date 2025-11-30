<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// 썸네일 생성 함수
function create_thumbnail($source_path, $destination_path, $width = 400, $height = 400) {
    list($source_width, $source_height, $source_type) = getimagesize($source_path);

    switch ($source_type) {
        case IMAGETYPE_JPEG: $source_image = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG:  $source_image = imagecreatefrompng($source_path); break;
        case IMAGETYPE_GIF:  $source_image = imagecreatefromgif($source_path); break;
        default: return false;
    }

    $source_aspect_ratio = $source_width / $source_height;
    $thumbnail_aspect_ratio = $width / $height;

    if ($source_width <= $width && $source_height <= $height) {
        copy($source_path, $destination_path);
        return true;
    }

    $new_width = $width;
    $new_height = $height;

    if ($thumbnail_aspect_ratio > $source_aspect_ratio) {
        $new_width = $height * $source_aspect_ratio;
    } else {
        $new_height = $width / $source_aspect_ratio;
    }

    $thumbnail_image = imagecreatetruecolor($new_width, $new_height);

    if ($source_type == IMAGETYPE_PNG) {
        imagealphablending($thumbnail_image, false);
        imagesavealpha($thumbnail_image, true);
        $transparent = imagecolorallocatealpha($thumbnail_image, 255, 255, 255, 127);
        imagefilledrectangle($thumbnail_image, 0, 0, $new_width, $new_height, $transparent);
    }

    imagecopyresampled($thumbnail_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);

    switch ($source_type) {
        case IMAGETYPE_JPEG: imagejpeg($thumbnail_image, $destination_path, 90); break;
        case IMAGETYPE_PNG:  imagepng($thumbnail_image, $destination_path, 9); break;
        case IMAGETYPE_GIF:  imagegif($thumbnail_image, $destination_path); break;
    }

    imagedestroy($source_image);
    imagedestroy($thumbnail_image);

    return true;
}

// --- 메인 로직 시작 ---

$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'];
$content = $_POST['content'];
$gallery_type = $_POST['gallery_type'];
$tags = $_POST['tags'] ?? ''; // 태그 받기
$tags = trim($tags); // 공백 제거

$is_private = isset($_POST['is_private']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$thumbnail_path = null;

$uploadDir = '../uploads/gallery/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

// 1. 파일 업로드로 썸네일 생성
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['thumbnail'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $originalFileName = 'original-' . uniqid() . '.' . $ext;
    $thumbFileName = 'thumb-' . uniqid() . '.' . $ext;
    $originalPath = $uploadDir . $originalFileName;
    $thumbPath = $uploadDir . $thumbFileName;

    if (move_uploaded_file($file['tmp_name'], $originalPath)) {
        if(create_thumbnail($originalPath, $thumbPath)) {
            $thumbnail_path = '/uploads/gallery/' . $thumbFileName;
        } else {
            $thumbnail_path = '/uploads/gallery/' . $originalFileName;
        }
    }
}

// 2. 파일이 없으면 본문 첫 이미지 추출
if (empty($thumbnail_path) && $post_id == 0) { // 새 글 작성일 때만 자동 추출 (수정 시 기존 유지 위해)
    preg_match('/<img[^>]+src="([^">]+)"/', $content, $matches);
    if (isset($matches[1])) {
        $first_image_url = $matches[1];
        // 외부 이미지가 아닐 경우에만 썸네일 생성 시도
        if(strpos($first_image_url, 'http') === false) {
             $first_image_path = '..' . $first_image_url;
             if (file_exists($first_image_path)) {
                $ext = strtolower(pathinfo($first_image_path, PATHINFO_EXTENSION));
                $thumbFileName = 'thumb-' . uniqid() . '.' . $ext;
                $thumbPath = $uploadDir . $thumbFileName;
                
                if(create_thumbnail($first_image_path, $thumbPath)) {
                    $thumbnail_path = '/uploads/gallery/' . $thumbFileName;
                } else {
                    $thumbnail_path = $first_image_url;
                }
             } else {
                 $thumbnail_path = $first_image_url;
             }
        } else {
            $thumbnail_path = $first_image_url;
        }
    }
}

// 비밀번호 해시 처리
$password_hash = null;
if ($is_private && !empty($password)) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
}

// --- DB 저장 로직 (여기가 중요하게 수정됨) ---

if ($post_id > 0) {
    // [수정] UPDATE 로직
    // 기본적으로 업데이트할 필드들
    $sql = "UPDATE gallery SET title=?, content=?, is_private=?, tags=?";
    $types = "ssis"; // string, string, int, string
    $params = [$title, $content, $is_private, $tags];

    // 썸네일이 새로 업로드 되었을 때만 SQL에 추가
    if ($thumbnail_path) {
        $sql .= ", thumbnail=?";
        $types .= "s";
        $params[] = $thumbnail_path;
    }
    
    // 비밀번호가 입력되었을 때만 SQL에 추가
    if ($password_hash) {
        $sql .= ", password_hash=?";
        $types .= "s";
        $params[] = $password_hash;
    }

    $sql .= " WHERE id=?";
    $types .= "i";
    $params[] = $post_id;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params); // 가변 인자 사용

} else {
    // [수정] INSERT 로직 (새 글 작성)
    // prepare를 먼저 하고 bind_param을 해야 합니다.
    $sql = "INSERT INTO gallery (gallery_type, title, content, thumbnail, is_private, password_hash, tags, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    
    // 타입: s(str), s, s, s, i(int), s, s
    $stmt->bind_param("ssssiss", $gallery_type, $title, $content, $thumbnail_path, $is_private, $password_hash, $tags);
}

if ($stmt->execute()) {
    $new_id = $post_id > 0 ? $post_id : $mysqli->insert_id;
    echo json_encode(['success' => true, 'redirect_url' => "#/gallery_view?id=" . $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => '저장 실패: ' . $stmt->error]);
}
$stmt->close();
?>