<?php
// /pages/page_view.php
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
?>

<style>
   
    .profile-container {
        display: flex;
        gap: 32px;
       
        position: absolute;
        top: 220px;
        left: 96px;
        width: 1250px;
       
        height: 605px;
    }
    .profile_left_menu {
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
    .profile_left_menu > a {
        color: #FFF;
        text-align: center;
        font-family: 'Fre1';
       
        font-size: 32px;
        font-style: normal;
        line-height: normal;
        cursor: pointer;
        text-decoration-line: none;
    }
   
    .profile_left_menu > a.active {
        font-family: 'Fre9';
       
    }
    .profile_main {
        width: 1016px;
        height: 100%;
        flex-shrink: 0;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 20px;
       
        padding-left: 30px;
        box-sizing: border-box;
        color: white;
       
        overflow-y: scroll;
    }

    .profile_main::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

   
    .profile_main .note-editor {
        background-color: white;
       
        color: black;
       
    }

    .content-display {}

    #edit-btn {
        padding: 10px;
        color: #000000;
        text-align: center;
        font-family: 'Fre9';
       
        font-size: 16px;
        font-style: normal;
        line-height: normal;
        background: #eee;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
    }
</style>

<div class="profile-container">
    <div class="profile_left_menu">
        <a class="<?php echo ($current_profile === 'eden') ? 'active' : ''; ?>" href="#/page_view?name=eden">Eden</a>
        <a class="<?php echo ($current_profile === 'white') ? 'active' : ''; ?>" href="#/page_view?name=white">White</a>
        <a class="<?php echo ($current_profile === 'rivlen') ? 'active' : ''; ?>" href="#/page_view?name=rivlen">Rivlen</a>
    </div>

    <div class="profile_main">
        <?php if ($is_admin): ?>
        <div id="view-mode">
            <div class="content-display">
                <?php echo !empty($page_content) ? $page_content : '<p>내용이 없습니다. 수정하기 버튼을 눌러 내용을 추가하세요.</p>'; ?>
            </div>
            <button type="button" id="edit-btn">수정하기</button>
        </div>
        <div id="edit-mode" style="display: none;">
            <form id="edit-form" action="ajax_save_page.php" method="post">
                <input
                    type="hidden"
                    name="page_name"
                    value="<?php echo htmlspecialchars($current_profile); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <textarea id="summernote" name="content"><?php echo htmlspecialchars($page_content); ?></textarea>
                <button
                    type="submit"
                    style="font-family:Fre7; background:rgb(255, 255, 255); color:black; border:1px; borde-radius 10px; padding: 5px 10px; cursor:pointer; margin-top:5px;">완료</button>
                <button
                    type="button"
                    id="cancel-btn"
                    style="font-family:Fre7; background:rgb(0, 0, 0); color:white; border:none; padding: 5px 10px; cursor:pointer; margin-top:5px;">취소</button>
            </form>
        </div>
    <?php else: ?>
        <div class="content-display">
            <?php echo !empty($page_content) ? $page_content : '<p>아직 작성된 내용이 없습니다.</p>'; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_admin): ?>
<script>
    $(document).ready(function () {
        
        $('#summernote').summernote({
            height: 450, 
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