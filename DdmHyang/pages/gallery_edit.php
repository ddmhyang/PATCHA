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
        <div class="deco-tape tape-1">Edit</div>
        <div class="deco-tape tape-2">Mode</div>

        <div class="left-section" id="gallery-edit-left">
            <i class="fa-solid fa-wrench floating-icon fi-1"></i>
            <div class="sub-title">System</div>
            <h1>Edit</h1>

            <a href="#/gallery_view?id=<?php echo $post_id; ?>" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> 뒤로 가기
            </a>
        </div>

        <div class="right-section-content" id="gallery-edit-right">
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

            <div id="imageOrderModal">
                <div class="modal-content">
                    <h3 style="margin-top:0;">이미지 순서 편집</h3>
                    <p style="font-size:13px; color:#666; margin-bottom:15px;">
                        <i class="fa-solid fa-arrows-up-down-left-right"></i> 이미지를 드래그하여 순서를 변경하세요.<br>
                        <b>순서대로 업로드</b> 버튼을 눌러야 본문에 삽입됩니다.
                    </p>
                    <div id="imageListContainer"></div>
                    <div class="modal-buttons">
                        <button type="button" class="btn-cancel" onclick="closeImageModal()">취소</button>
                        <button type="button" class="btn-confirm" onclick="confirmImageUpload()">순서대로 업로드</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    // [중요] let 대신 var를 사용하여 재선언 오류(Text Box 현상) 방지
    var pendingFiles = [];
    var currentEditor = null;
    var dragStartIndex = null;

    $(document).ready(function() {
        // 페이지가 열릴 때마다 목록 초기화
        pendingFiles = [];

        $('.summernote').summernote({
            height: 400,
            callbacks: {
                onImageUpload: function(files) {
                    currentEditor = $(this);
                    // 새로 선택한 파일들을 목록에 '추가' (기존 목록 유지)
                    var newFiles = Array.from(files);
                    pendingFiles = pendingFiles.concat(newFiles);
                    openImageModal();
                }
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

    // 팝업창 열기 및 목록 그리기
    window.openImageModal = function() {
        $('#imageOrderModal').css('display', 'flex');
        renderImageList();
    };

    // 팝업창 닫기 (취소 시 목록 초기화)
    window.closeImageModal = function() {
        $('#imageOrderModal').hide();
        pendingFiles = []; 
    };

    // 이미지 목록 렌더링
    window.renderImageList = function() {
        var container = document.getElementById('imageListContainer');
        container.innerHTML = '';

        if (pendingFiles.length === 0) {
            container.innerHTML = '<div style="text-align:center; padding:20px; color:#999;">선택된 이미지가 없습니다.</div>';
            return;
        }

        pendingFiles.forEach(function(file, index) {
            var div = document.createElement('div');
            div.className = 'image-list-item';
            div.setAttribute('draggable', 'true');
            div.dataset.index = index;

            // 드래그 이벤트 연결
            div.addEventListener('dragstart', dragStart);
            div.addEventListener('dragover', dragOver);
            div.addEventListener('drop', dragDrop);
            div.addEventListener('dragenter', dragEnter);
            div.addEventListener('dragleave', dragLeave);
            
            var reader = new FileReader();
            reader.onload = function(e) {
                div.innerHTML = `
                    <div style="display:flex; align-items:center;">
                        <span style="margin-right:10px; color:#888; font-weight:bold;">${index + 1}</span>
                        <img src="${e.target.result}" class="image-preview">
                        <div class="file-info">
                            <span class="file-name">${file.name}</span>
                            <span class="file-size">${(file.size / 1024).toFixed(1)} KB</span>
                        </div>
                    </div>
                    <button type="button" class="delete-btn" onclick="removeImage(${index})">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;
            };
            reader.readAsDataURL(file);
            container.appendChild(div);
        });
    };

    // --- 드래그 앤 드롭 로직 ---
    window.dragStart = function(e) {
        dragStartIndex = +this.dataset.index;
        this.classList.add('dragging');
    };
    window.dragOver = function(e) { e.preventDefault(); };
    window.dragEnter = function(e) { this.classList.add('over'); };
    window.dragLeave = function(e) { this.classList.remove('over'); };
    window.dragDrop = function(e) {
        var dragEndIndex = +this.dataset.index;
        swapItems(dragStartIndex, dragEndIndex);
        this.classList.remove('dragging');
    };

    window.swapItems = function(fromIndex, toIndex) {
        if (fromIndex === toIndex) return;
        var itemToMove = pendingFiles[fromIndex];
        pendingFiles.splice(fromIndex, 1);
        pendingFiles.splice(toIndex, 0, itemToMove);
        renderImageList();
    };

    window.removeImage = function(index) {
        pendingFiles.splice(index, 1);
        renderImageList();
    };

    // --- 최종 업로드 로직 ---
    window.confirmImageUpload = async function() {
        if (pendingFiles.length === 0) {
            alert("업로드할 이미지가 없습니다.");
            return;
        }

        $('#imageOrderModal').hide(); 
        var htmlContent = ''; 

        // 사용자가 정한 순서대로 하나씩 서버에 업로드 (자동 삽입 X)
        for (var i = 0; i < pendingFiles.length; i++) {
            try {
                // main.js의 수정된 함수 호출 (3번째 인자 false = 자동삽입 안함)
                var response = await uploadSummernoteImage(pendingFiles[i], currentEditor, false);
                if (response && response.success && response.url) {
                    // HTML 태그 생성 후 문자열에 추가 (100% 너비 적용)
                    htmlContent += `<p><img src="${response.url}" style="width: 100%;"></p>`;
                }
            } catch (e) {
                console.error("업로드 오류:", e);
            }
        }
        
        // 모든 업로드가 끝나면 모아둔 HTML을 한 번에 에디터에 삽입
        if (htmlContent) {
            currentEditor.summernote('pasteHTML', htmlContent);
        }
        pendingFiles = []; // 목록 초기화
    };
</script>