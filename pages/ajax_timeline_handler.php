<?php
// /pages/ajax_timeline_handler.php
require_once '../includes/db.php';

function get_first_image_from_html($html) {
    if (preg_match('/<img[^>]+src="([^">]+)"/', $html, $matches)) {
        return $matches[1];
    }
    return null;
}

if (empty($_POST['action'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '잘못된 요청입니다. "action" 파라미터가 필요합니다.']);
    exit;
}

$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

if ($_POST['action'] !== 'get_detail') {
    header('Content-Type: application/json');
}

if ($_POST['action'] !== 'get_detail') {
    if (!$is_admin) {
        echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
        exit;
    }
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => '보안 토큰이 유효하지 않습니다.']);
        exit;
    }
}

function get_title_from_html($html) {
    if (empty($html)) { return '제목 없음'; }
    $text = strip_tags($html);
    $lines = preg_split('/\\r\\n|\\r|\\n/', $text);
    foreach ($lines as $line) { if (trim($line) !== '') { return trim($line); } }
    return '제목 없음';
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_detail':
        header('Content-Type: text/html');
        $item_id = intval($_POST['id'] ?? 0);
        if ($item_id <= 0) { exit('잘못된 ID입니다.'); }
        $stmt = $mysqli->prepare("SELECT * FROM eden_timeline WHERE id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$item) { exit('<h1>항목을 찾을 수 없습니다.</h1>'); }
        ?>
<style>
    .detail-container {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        color: white;
        padding: 20px 30px;
        box-sizing: border-box;
        overflow-y: auto;
    }
    .detail-container::-webkit-scrollbar {
        width: 8px;
    }
    .detail-container::-webkit-scrollbar-thumb {
        background-color: #555;
        border-radius: 4px;
    }
    .detail-container::-webkit-scrollbar-track {
        background-color: #333;
    }
    .detail-container h1,
    .detail-container p {
        margin: 0;
    }
    .detail-container hr {
        margin: 20px 0;
        border-color: rgba(255,255,255,0.2);
    }
    #detail-edit-form input[type="text"] {
        width: 100%;
        margin-bottom: 15px;
        padding: 8px;
        box-sizing: border-box;
        background: #333;
        color: white;
        border: 1px solid #555;
    }
    #detail-edit-form label {
        display: block;
        margin-bottom: 5px;
        font-family: 'fre9';
    }
    #detail-edit-controls button,
    #detail-view-controls button {
        margin-top: 50px;
        background: #eee;
        border: none;
        padding: 10px 15px;
        cursor: pointer;
        margin-right: 10px;
        font-family: 'fre7';
    }
    #current-thumbnail-preview {
        max-width: 100px;
        max-height: 100px;
        border: 1px solid #555;
        margin-top: 5px;
        display: block;
    }
</style>
<div class="detail-container">
    <div id="detail-view-mode">
        <h1><?php echo htmlspecialchars($item['year']); ?>
            -
            <?php echo htmlspecialchars(get_title_from_html($item['full_description'])); ?></h1><hr>
        <div id="detail-content-display"><?php echo $item['full_description']; ?></div>
        <?php if ($is_admin): ?>
        <div id="detail-view-controls">
            <button id="detail-edit-btn">수정하기</button>
            <button
                id="detail-delete-btn"
                data-id="<?php echo $item['id']; ?>"
                style="background-color:rgb(0, 0, 0); color: white; font-family: 'fre9';">삭제하기</button>
        </div><?php endif; ?>
    </div>
    <?php if ($is_admin): ?>
    <div id="detail-edit-mode" style="display:none;">
        <form id="detail-edit-form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
            <input
                type="hidden"
                name="existing_thumbnail"
                value="<?php echo htmlspecialchars($item['thumbnail']); ?>">
            <input type="hidden" name="action" value="update_full_item">
            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo $_SESSION['csrf_token']; ?>">

            <label>제목:</label><input
                type="text"
                name="year"
                value="<?php echo htmlspecialchars($item['year']); ?>">

            <label>썸네일 이미지:</label><input
                type="file"
                name="thumbnail_file"
                accept="image/*"
                style="background:transparent; border:none;">
            <p style="font-size:12px; opacity:0.7; margin-top:5px;">(새 파일을 올리면 썸네일이 교체됩니다.)</p>

            <label style="margin-top:15px;">상세 설명:</label>
            <textarea id="detail-summernote" name="full_description"><?php echo htmlspecialchars($item['full_description']); ?></textarea>
            <div id="detail-edit-controls">
                <button
                    type="submit"
                    style="font-family:Fre7; background:rgb(255, 255, 255); color:black; border:1px; borde-radius 10px; padding: 5px 10px; cursor:pointer; margin-top:5px;">저장</button>
                <button
                    type="button"
                    id="detail-cancel-btn"
                    style="font-family:Fre7; background:rgb(0, 0, 0); color:white; border:none; padding: 5px 10px; cursor:pointer; margin-top:5px;">취소</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    <hr>
    <a href="#" id="back-to-timeline-btn" style="color:white;">&laquo; 타임라인으로 돌아가기</a>
</div>
<?php
        break;

    case 'update_full_item':
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '수정할 항목의 ID가 없습니다.']);
            exit;
        }
        $year = $_POST['year'] ?? '-';
        $desc = $_POST['full_description'] ?? '';
        $thumbnail_path = $_POST['existing_thumbnail'] ?? '';

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] == 0) {
            $uploadDir = '../uploads/thumbnails/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $file = $_FILES['thumbnail_file'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'thumb_' . $id . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $thumbnail_path = $targetPath;
            } else {
                echo json_encode(['success' => false, 'message' => '썸네일 파일 업로드에 실패했습니다.']);
                exit;
            }
        } else {
            $image_from_content = get_first_image_from_html($desc);
            if (empty($thumbnail_path) && !empty($image_from_content)) {
                $thumbnail_path = $image_from_content;
            }
        }

        $stmt = $mysqli->prepare("UPDATE eden_timeline SET year = ?, thumbnail = ?, full_description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $year, $thumbnail_path, $desc, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB 업데이트 실패: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'add_full_item':
        $year = $_POST['year'] ?? '-';
        $desc = $_POST['full_description'] ?? '새로운 이야기';
        $thumbnail_path = '';

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] == 0) {
            $uploadDir = '../uploads/thumbnails/';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $file = $_FILES['thumbnail_file'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'thumb_new_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $thumbnail_path = $targetPath;
            }
        } else {
            $image_from_content = get_first_image_from_html($desc);
            if (!empty($image_from_content)) {
                $thumbnail_path = $image_from_content;
            }
        }

        $max_order_res = $mysqli->query("SELECT MAX(sort_order) as max_order FROM eden_timeline");
        $max_order = $max_order_res->fetch_assoc()['max_order'] ?? 0;
        $new_order = $max_order + 10;
        $stmt = $mysqli->prepare("INSERT INTO eden_timeline (year, thumbnail, full_description, sort_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $year, $thumbnail_path, $desc, $new_order);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'DB 추가 실패: ' . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'reorder':
        header('Content-Type: application/json');
        $order_data = $_POST['order'] ?? [];
        if (empty($order_data)) { echo json_encode(['success' => false, 'message' => '순서 데이터가 없습니다.']); exit; }
        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("UPDATE eden_timeline SET sort_order = ? WHERE id = ?");
            foreach ($order_data as $item) {
                $sort_order = (int)$item['sort_order'];
                $id = (int)$item['id'];
                $stmt->bind_param("ii", $sort_order, $id);
                $stmt->execute();
            }
            $stmt->close();
            $mysqli->commit();
            echo json_encode(['success' => true]);
        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
        }
        break;
        
    case 'delete_batch':
        header('Content-Type: application/json');
        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) { echo json_encode(['success' => false, 'message' => '삭제할 ID가 없습니다.']); exit; }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $stmt = $mysqli->prepare("DELETE FROM eden_timeline WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$ids);
        if ($stmt->execute()) { echo json_encode(['success' => true]); }
        else { echo json_encode(['success' => false, 'message' => $stmt->error]); }
        $stmt->close();
        break;
        
    case 'delete_item':
        header('Content-Type: application/json');
        if (!$is_admin || !isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => '권한 또는 토큰이 유효하지 않습니다.']);
            exit;
        }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '유효하지 않은 ID입니다.']);
            exit;
        }
        $stmt = $mysqli->prepare("DELETE FROM eden_timeline WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => '알 수 없는 요청입니다.']);
        break;
}

if ($action !== 'get_detail') {
    $mysqli->close();
}
?>