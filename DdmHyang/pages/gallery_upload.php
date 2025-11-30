<?php
require_once '../includes/db.php';
if (!$is_admin) { die("권한이 없습니다."); }
$gallery_type = $_GET['type'] ?? 'gallery';
?>

<div class="page-container" id="main_content">
    <div class="main-frame">
        <div class="deco-tape tape-1">New</div>
        <div class="deco-tape tape-2">Post</div>

        <div class="left-section">
            <i class="fa-solid fa-pencil floating-icon fi-1"></i>
            <i class="fa-solid fa-feather floating-icon fi-2" style="transform: rotate(-20deg);"></i>

            <div class="sub-title">Write</div>
            <h1>Upload</h1>
            <p class="description">
                새로운 이야기를<br>기록해보세요.
            </p>

            <a href="#/<?php echo htmlspecialchars($gallery_type); ?>" class="back-btn">
                <i class="fa-solid fa-times"></i> 작성 취소
            </a>
        </div>

        <div class="right-section-content">
            <form class="ajax-form styled-form" action="ajax_save_gallery.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="gallery_type" value="<?php echo htmlspecialchars($gallery_type); ?>">
                
                <div class="input-group">
                    <label>제목</label>
                    <input type="text" name="title" required placeholder="제목을 입력하세요">
                </div>

                <div class="input-group">
                    <label>해쉬태그 (쉼표 , 로 구분해서 입력)</label>
                    <input type="text" name="tags" placeholder="예: 판타지, 로맨스, 성장물" 
                        value="<?php echo isset($post['tags']) ? htmlspecialchars($post['tags']) : ''; ?>">
                </div>

                <div class="input-group">
                    <label>썸네일 (선택)</label>
                    <input type="file" name="thumbnail" class="file-input">
                </div>

                <div class="input-group" style="flex-direction: row; align-items: center; gap: 10px;">
                    <input type="checkbox" id="is_private" name="is_private" value="1" style="width: auto;">
                    <label for="is_private" style="margin:0;">비밀글 설정</label>
                    <input type="password" id="password" name="password" placeholder="비밀번호" style="display:none; width: 150px; margin-left: 10px;">
                </div>

                <div class="input-group">
                    <label>내용</label>
                    <textarea class="summernote" name="content"></textarea>
                </div>

                <button class="action-btn" type="submit" style="width: 100%; justify-content: center; margin-top: 20px;">
                    <i class="fa-solid fa-check"></i> 저장하기
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