<?php

if (!isset($mysqli)) {
    $redirect_name = $_GET['name'] ?? 'eden';
    header("Location: main.php?page=page_view&name=" . $redirect_name);
    exit;
}

$current_profile = $_GET['name'] ?? 'eden';

$stmt = $mysqli->prepare("SELECT content FROM eden_pages_content WHERE page_name = ?");
$stmt->bind_param("s", $current_profile);
$stmt->execute();
$result = $stmt->get_result();
$page_content_row = $result->fetch_assoc();

$page_content = $page_content_row['content'] ?? '';
$stmt->close();

$profile_titles = [
    'eden' => 'Eden',
    'white' => 'White',
    'rivlen' => 'Rivlen'
];
$current_title = $profile_titles[$current_profile] ?? ucfirst($current_profile);
?>

<div class="gallery-page-wrapper">
    <div class="left-menu">
        <a
            class="<?php echo ($current_profile === 'eden') ? 'active' : ''; ?>"
            href="#/page_view?name=eden">Eden</a>
        <a
            class="<?php echo ($current_profile === 'white') ? 'active' : ''; ?>"
            href="#/page_view?name=white">White</a>
        <a
            class="<?php echo ($current_profile === 'rivlen') ? 'active' : ''; ?>"
            href="#/page_view?name=rivlen">Rivlen</a>
    </div>

    <div class="gallery-content-area">
        <h1 class="gallery-main-title"><?php echo $current_title; ?></h1>

        <div class="profile-content">
            <?php if ($is_admin): ?>
            <div id="view-mode">
                <div class="edit-button-container">
                    <button type="button" id="edit-btn">수정하기</button>
                </div>
                <div class="content-display">
                    <?php echo !empty($page_content) ? $page_content : '<p>내용이 없습니다. 수정하기 버튼을 눌러 내용을 추가하세요.</p>'; ?>
                </div>
            </div>
            <div id="edit-mode" style="display: none;">
                <form id="edit-form" action="ajax_save_page.php" method="post">
                    <input
                        type="hidden"
                        name="page_name"
                        value="<?php echo htmlspecialchars($current_profile); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <textarea id="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
                    <button type="submit" class="btn-submit">완료</button>
                    <button type="button" id="cancel-btn" class="btn-cancel">취소</button>
                </form>
            </div>
        <?php else: ?>
            <div class="content-display">
                <?php echo !empty($page_content) ? $page_content : '<p>아직 작성된 내용이 없습니다.</p>'; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>

    .gallery-page-wrapper {
        display: flex;
        gap: 32px;
        position: absolute;
        top: 220px;
        left: 96px;
        width: 1250px;
        height: 605px;
    }
    .left-menu {
        width: 204px;
        height: 100%;
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        display: flex;
        flex-direction: column;
        gap: 140px;
        padding-top: 107px;
        box-sizing: border-box;
    }
    .left-menu > a {
        color: #FFF;
        text-align: center;
        font-family: 'Fre1';
        font-size: 32px;
        cursor: pointer;
        text-decoration-line: none;
    }
    .left-menu > a.active {
        font-family: 'Fre9';
    }
    .gallery-content-area {
        width: 1016px;
        height: 100%;
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 20px 30px;
        box-sizing: border-box;
        color: white;
        overflow-y: scroll;
    }
    .gallery-content-area::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
    .gallery-main-title {
        text-align: center;
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 40px;
        margin-top: 40px;
        margin-bottom: 20px;
    }

    .profile-content {
        padding: 10px;
    }
    .content-display {
        min-height: 400px;
    }

    .edit-button-container {
        text-align: right;

        padding-right: 20px;
        margin-bottom: 15px;

    }

    #edit-btn,
    .btn-cancel,
    .btn-submit {
        border: none;
        padding: 8px 15px;
        cursor: pointer;
        border-radius: 5px;
        font-family: 'Fre9';
        font-size: 16px;
        margin-top: 10px;
    }
    #edit-btn {
        background: #eee;
        color: #000;
    }
    .btn-submit {
        background: white;
        color: black;
    }
    .btn-cancel {
        background: black;
        color: white;
        margin-left: 5px;
    }

    .note-editor.note-frame {
        background-color: rgba(0, 0, 0, 0.6) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        border-radius: 5px !important;
    }
    .note-editor .note-editing-area .note-editable {
        background-color: rgba(0, 0, 0, 0.4) !important;
        color: white !important;
        min-height: 250px !important;

        padding: 15px !important;
    }
    .note-toolbar {
        background-color: rgba(0, 0, 0, 0.7) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
    }

    @media (max-width: 768px) {
        .gallery-page-wrapper {
            position: static;
            display: block;
            height: auto;
        }
        .gallery-content-area {
            position: absolute !important;
            top: 273px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 804px;
            padding: 60px 40px;
            margin: 0;
            overflow-y: scroll;
        }
        .left-menu {
            width: 100%;
            height: 150px;
            flex-direction: row;
            position: absolute;
            bottom: 0;
            background: linear-gradient(180deg, rgb(0, 0, 0) 0%, rgba(0, 0, 0) 100%);
            left: 0;
            padding-top: 0;
            gap: 82px;
        }
        .left-menu > a {
            margin: 60px 0 0 83px;
        }
        .gallery-main-title {
            font-size: 40px;
            margin-top: 0;
            text-align: left;
        }
        .note-editor .note-editing-area .note-editable {
            min-height: 350px !important;
        }
    }
</style>

<?php if ($is_admin): ?>
<script>
    $(document).ready(function () {
        $('#summernote').summernote({
            height: 250,
            dialogsInBody: true,
            callbacks: {
                onImageUpload: function (files) {
                    uploadImage(files[0], $(this));
                }
            }
        });

        $('#edit-btn').on('click', function () {
            $('#view-mode').hide();
            $('#edit-mode').show();
        });
        $('#cancel-btn').on('click', function () {
            $('#edit-mode').hide();
            $('#view-mode').show();
        });

        $('#edit-form').on('submit', function (e) {
            e.preventDefault();
            $('textarea[name="content"]').val($('#summernote').summernote('code'));
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('성공적으로 저장되었습니다.');
                        $('#view-mode .content-display').html($('#summernote').summernote('code'));
                        $('#edit-mode').hide();
                        $('#view-mode').show();
                    } else {
                        alert('저장 실패: ' + response.message);
                    }
                }
            });
        });

        function uploadImage(file, editor) {
            let data = new FormData();
            data.append("file", file);
            $.ajax({
                url: 'ajax_upload_image.php',
                type: "POST",
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (url) {
                    editor.summernote('insertImage', url);
                }
            });
        }
    });
</script>
<?php endif; ?>