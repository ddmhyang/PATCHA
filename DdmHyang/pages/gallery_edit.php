<?php
require_once '../includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) { die("유효하지 않은 게시물입니다."); }

$stmt = $mysqli->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) { die("게시물이 없습니다."); }
?>

<div class="page-container" id="main_content">
    <div class="main-frame edit-mode">
        <div class="edit-container">
            <div class="edit-header">
                <i class="fa-solid fa-wrench floating-icon fi-1"></i>
                <div class="edit-title">System</div>
                <h1>Edit</h1>

                <a href="#/gallery_view?id=<?php echo $post_id; ?>" class="back-btn">
                    <i class="fa-solid fa-arrow-left"></i> 뒤로 가기
                </a>
            </div>

            <div class="edit-body">
                <form class="ajax-form styled-form" action="ajax_save_gallery.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                    <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($post['gallery_type']); ?>">
                    
                    <div class="input-group">
                        <label>제목</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                    </div>

                    <div class="input-group">
                        <label>해쉬태그 (쉼표 , 로 구분해서 입력)</label>
                        <input type="text" name="tags" placeholder="예: 판타지, 로맨스, 성장물" 
                            value="<?php echo isset($post['tags']) ? htmlspecialchars($post['tags']) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <label>썸네일 변경 (선택)</label>
                        <input type="file" name="thumbnail" class="file-input">
                    </div>

                    <div class="input-group" style="flex-direction: row; align-items: center; gap: 10px;">
                        <input type="checkbox" id="is_private" name="is_private" value="1" <?php if($post['is_private']) echo 'checked'; ?>>
                        <label for="is_private" style="margin:0;">비밀글</label>
                        <input type="password" id="password" name="password" placeholder="비번 변경시에만 입력" 
                            style="<?php if(!$post['is_private']) echo 'display:none;'; ?> width: 180px; margin-left: 10px;">
                    </div>

                    <div class="input-group">
                        <label>내용</label>
                        <textarea class="summernote" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>

                    <button class="action-btn" type="submit" style="width: 100%; justify-content: center; margin-top: 20px;">
                        <i class="fa-solid fa-check"></i> 수정 완료
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
var pendingFiles = [];
    var currentEditor = null;
    var dragStartIndex = null;

    $(document).ready(function() {
        pendingFiles = [];

        $('.summernote').summernote({
            minHeight: 400,
            placeholder: '여기에 내용을 입력해주세요...',
            disableDragAndDrop: true,
            lang: 'ko-KR',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['insert', ['table', 'link', 'picture', 'video', 'hr']],
                ['view', ['codeview', 'help']]
            ],
            styleTags: [
                'p', 
                { title: '제목 1', tag: 'h1', className: 'custom-h1', value: 'h1' },
                { title: '제목 2', tag: 'h2', className: 'custom-h2', value: 'h2' },
                { title: '제목 3', tag: 'h3', className: 'custom-h3', value: 'h3' },
                'blockquote'
            ],
            callbacks: {
                onImageUpload: function(files) {
                    for (var i = 0; i < files.length; i++) {
                        if (window.uploadSummernoteImage) {
                            window.uploadSummernoteImage(files[i], this);
                        }
                    }
                },
                onFocus: function() {
                    $(this).closest('.note-editor').addClass('focused');
                },
                onBlur: function() {
                    $(this).closest('.note-editor').removeClass('focused');
                }
            }
        });

        $('#is_private').on('change', function() {
            $('#password').toggle(this.checked).prop('required', this.checked);
        });
    });
</script>