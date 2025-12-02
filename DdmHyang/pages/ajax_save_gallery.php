<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

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


$post_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = $_POST['title'];
$content = $_POST['content'];
$gallery_type = $_POST['gallery_type'];
$tags = $_POST['tags'] ?? ''; 
$tags = trim($tags); 

$is_private = isset($_POST['is_private']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$thumbnail_path = null;

$uploadDir = '../uploads/gallery/';
if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

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

if (empty($thumbnail_path)) { 
    preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/', $content, $matches);
    
    if (isset($matches[1])) {
        $first_image_url = $matches[1];
        
        if(strpos($first_image_url, 'http') === false) {
             $first_image_path = '..' . $first_image_url;
             
             if (file_exists($first_image_path)) {
                $ext = strtolower(pathinfo($first_image_path, PATHINFO_EXTENSION));
                if (!$ext) $ext = 'jpg'; 
                
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


if ($post_id > 0) {
    $sql = "UPDATE gallery SET title=?, content=?, is_private=?, tags=?";
    $types = "ssis";
    $params = [$title, $content, $is_private, $tags];

    if ($thumbnail_path) {
        $sql .= ", thumbnail=?";
        $types .= "s";
        $params[] = $thumbnail_path;
    }
    
    if ($password_hash) {
        $sql .= ", password_hash=?";
        $types .= "s";
        $params[] = $password_hash;
    }

    $sql .= " WHERE id=?";
    $types .= "i";
    $params[] = $post_id;

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params); 

} else {
    $sql = "INSERT INTO gallery (gallery_type, title, content, thumbnail, is_private, password_hash, tags, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    
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