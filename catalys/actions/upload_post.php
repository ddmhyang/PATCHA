<?php
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='../login.php';</script>";
    exit;
}

$board_type = isset($_GET['board']) ? $_GET['board'] : '';
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$table_name = 'posts_' . $board_type;

$is_edit = $post_id > 0;
$post = ['title' => '', 'content' => ''];

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
        body { font-family: sans-serif; padding: 20px; }
        .container { max-width: 960px; margin: 0 auto; }
        input[type=text] { width: 100%; padding: 10px; margin-bottom: 10px; }
        .btn-group { margin-top: 10px; }
        .btn { padding: 10px 15px; background-color: #333; color: #fff; border: none; cursor: pointer; }
        .btn-cancel { background-color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $is_edit ? '게시글 수정' : '게시글 작성' ?></h1>
        <form id="postForm" action="save_post.php" method="post">
            <input type="hidden" name="board_type" value="<?= htmlspecialchars($board_type) ?>">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">

            <input type="text" id="title" name="title" placeholder="제목을 입력하세요" value="<?= htmlspecialchars($post['title']) ?>" required>
            <textarea id="summernote" name="content"><?= htmlspecialchars($post['content']) ?></textarea>

            <div class="btn-group">
                <button type="submit" class="btn">저장</button>
                <button type="button" class="btn btn-cancel" onclick="history.back()">취소</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#summernote').summernote({
            height: 300,
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
                    console.error(textStatus + " " + errorThrown);
                }
            });
        }
    });
    </script>
</body>
</html>