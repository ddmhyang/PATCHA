<?php
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='../login.php';</script>";
    exit;
}

$board_type = isset($_GET['board']) ? $_GET['board'] : '';
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$table_name = 'posts_' . $board_type;

$allowed_tables = ['posts_for', 'posts_log', 'posts_sp', 'posts_etc'];
if (!in_array($table_name, $allowed_tables)) {
    die('잘못된 게시판 접근입니다.');
}

$is_edit = $post_id > 0;
$post = ['title' => '', 'content' => '', 'thumbnail' => ''];

if ($is_edit) {
    $sql = "SELECT * FROM {$table_name} WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?= $is_edit ? '게시글 수정' : '게시글 작성' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 960px; margin: 0 auto; background-color: #fff; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type=text] { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        #thumbnail_preview { max-width: 300px; max-height: 200px; margin-top: 10px; border: 1px solid #ddd; }
        .btn-group { margin-top: 20px; text-align: right; }
        .btn { padding: 10px 20px; background-color: #333; color: #fff; border: none; cursor: pointer; border-radius: 4px; font-size: 16px; }
        .btn-cancel { background-color: #999; margin-left: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $is_edit ? '게시글 수정' : '게시글 작성' ?></h1>
        <form id="postForm" action="save_post.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="board_type" value="<?= htmlspecialchars($board_type) ?>">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">

            <div class="form-group">
                <label for="title">제목</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="thumbnail">썸네일 이미지</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                <div id="thumbnail_preview_container">
                    <?php if (!empty($post['thumbnail'])): ?>
                        <img id="thumbnail_preview" src="../<?= htmlspecialchars($post['thumbnail']) ?>" alt="썸네일 미리보기">
                    <?php else: ?>
                        <img id="thumbnail_preview" src="" alt="썸네일 미리보기" style="display:none;">
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="summernote">본문</label>
                <textarea id="summernote" name="content"><?= htmlspecialchars($post['content']) ?></textarea>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-cancel" onclick="history.back()">취소</button>
                <button type="submit" class="btn">저장</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 400,
            callbacks: {
                onImageUpload: function(files) {
                    for (var i = 0; i < files.length; i++) {
                        uploadImage(files[i], this);
                    }
                }
            }
        });

        function uploadImage(file, editor) {
            var data = new FormData();
            data.append("file", file);
            $.ajax({
                url: 'upload_image.php',
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: 'POST',
                success: function(url) {
                    $(editor).summernote('insertImage', url);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('이미지 업로드에 실패했습니다: ' + textStatus);
                }
            });
        }

        // 썸네일 미리보기 기능
        $('#thumbnail').on('change', function(event) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#thumbnail_preview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(event.target.files[0]);
        });
    });
    </script>
</body>
</html>