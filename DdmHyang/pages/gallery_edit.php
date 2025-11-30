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
    <div class="main-frame">
        <div class="deco-tape tape-1">Edit</div>
        <div class="deco-tape tape-2">Mode</div>

        <div class="left-section">
            <i class="fa-solid fa-wrench floating-icon fi-1"></i>
            <div class="sub-title">System</div>
            <h1>Edit</h1>
            <p class="description">내용을 수정합니다.</p>

            <a href="#/gallery_view?id=<?php echo $post_id; ?>" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> 뒤로 가기
            </a>
        </div>

        <div class="right-section-content">
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

<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            height: 400,
            callbacks: {
                onImageUpload: function(files) { uploadSummernoteImage(files[0], $(this)); }
            },
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['codeview', 'help']]
            ]
        });
        $('#is_private').on('change', function() {
            $('#password').toggle(this.checked).prop('required', this.checked);
        });
    });
</script>