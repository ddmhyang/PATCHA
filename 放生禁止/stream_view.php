<?php
require_once 'includes/db.php';

// 1. 게시글 정보 가져오기
$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) die("잘못된 접근입니다.");

$stmt = $mysqli->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) die("게시글이 없습니다.");

// 2. 블록들 가져오기
$blocks_sql = "SELECT * FROM post_blocks WHERE gallery_id = $post_id ORDER BY id ASC";
$blocks = $mysqli->query($blocks_sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="view-container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <?php if ($is_admin): ?>
            <button class="btn-action delete-gallery-btn" data-id="<?php echo $post_id; ?>">글 삭제</button>
        <?php endif; ?>
    </div>
    <hr>

    <div id="block-stream">
        <?php foreach ($blocks as $block): ?>
            <div class="content-block type-<?php echo $block['block_type']; ?>" id="block-<?php echo $block['id']; ?>">
                
                <div class="block-content">
                    <?php 
                    // ★★★ 여기가 핵심! 타입이 'Image'면 그림 태그를, 아니면 글자를 출력합니다.
                    if ($block['block_type'] === 'Image'): ?>
                        <img src="<?php echo htmlspecialchars($block['content']); ?>" alt="Block Image">
                    <?php else: ?>
                        <?php echo $block['content']; // 이미 DB에 <br> 등이 처리되어 있음 ?>
                    <?php endif; ?>
                </div>

                <?php if ($is_admin): ?>
                    <button class="btn-del-block" onclick="deleteBlock(<?php echo $block['id']; ?>)">×</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($is_admin): ?>
    <div class="admin-block-adder">
        <textarea id="new-block-text" placeholder="텍스트 내용을 입력하세요..."></textarea>
        
        <input type="file" id="block-image-input" accept="image/*" style="display: none;">

        <div class="adder-btns">
            <button type="button" class="btn-type-a" onclick="addBlock('A')">A 타입 (글)</button>
            <button type="button" class="btn-type-b" onclick="addBlock('B')">B 타입 (박스)</button>
            <button type="button" class="btn-type-img" onclick="$('#block-image-input').click()">📷 사진 추가</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="post-actions">
        <a href="#/gallery" class="btn-back-to-list">목록으로</a>
    </div>
</div>

<style>
    /* 블록 공통 스타일 */
    .content-block {
        position: relative;
        margin-bottom: 20px;
        padding: 15px 20px;
        border-radius: 10px;
        font-family: 'Fre3';
        font-size: 18px;
        line-height: 1.6;
        transition: all 0.3s;
    }
    
    /* TYPE A: 투명 배경 + 왼쪽 파란 줄 */
    .content-block.type-A {
        background: rgba(255,255,255,0.5);
        border-left: 5px solid #7078A7;
        color: #595959;
    }

    /* TYPE B: 회색 박스 */
    .content-block.type-B {
        background: #F0F0F5;
        border: 1px solid #D4D4D4;
        color: #333;
    }

    /* ★ TYPE Image: 이미지는 배경색 없이 그림만 크게 */
    .content-block.type-Image {
        padding: 0; /* 여백 제거 */
        background: transparent;
        text-align: center; /* 중앙 정렬 */
    }
    .content-block.type-Image img {
        max-width: 100%; /* 화면 꽉 차게 */
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    /* 삭제 버튼 */
    .btn-del-block {
        position: absolute; top: 5px; right: 5px;
        border: none; background: #fff; border-radius: 50%; width:25px; height:25px;
        color: #ff6b6b; cursor: pointer; opacity: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .content-block:hover .btn-del-block { opacity: 1; }

    /* 입력창 스타일 */
    .admin-block-adder {
        margin-top: 40px; padding: 20px;
        background: #f9f9f9; border: 2px dashed #7078A7; border-radius: 15px;
    }
    .admin-block-adder textarea {
        width: 100%; height: 80px; padding: 10px;
        border: 1px solid #ccc; border-radius: 10px; font-family: 'Fre3';
    }
    .adder-btns { margin-top: 10px; text-align: right; }
    .adder-btns button {
        padding: 8px 16px; margin-left: 5px; border-radius: 8px; cursor: pointer; border:none;
    }
    .btn-type-a { background: white; border: 2px solid #7078A7; color: #7078A7; }
    .btn-type-b { background: #7078A7; color: white; }
    .btn-type-img { background: #555; color: white; } /* 사진 버튼 색상 */
</style>

<script>
// 1. 텍스트 블록 추가 함수
function addBlock(type) {
    let text = $('#new-block-text').val();
    if (!text.trim()) { alert("내용을 입력해주세요."); return; }
    saveBlockToServer(type, text);
}

// 2. 이미지 파일 선택 시 자동 업로드 및 블록 추가
$('#block-image-input').on('change', function() {
    let file = this.files[0];
    if (!file) return;

    let formData = new FormData();
    formData.append('file', file);

    // 먼저 이미지를 서버에 올립니다 (기존 ajax_upload_image.php 사용)
    $.ajax({
        url: 'ajax_upload_image.php', 
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // 업로드 성공! 받은 이미지 주소(url)를 내용으로 하는 'Image' 타입 블록을 만듭니다.
                saveBlockToServer('Image', response.url);
                $('#block-image-input').val(''); // 초기화
            } else {
                alert('이미지 업로드 실패: ' + response.message);
            }
        },
        error: function() { alert('서버 통신 오류'); }
    });
});

// 3. 서버에 블록 저장 요청 (공통 함수)
function saveBlockToServer(type, content) {
    $.ajax({
        url: 'ajax_add_block_to_post.php',
        type: 'POST',
        data: {
            gallery_id: <?php echo $post_id; ?>,
            type: type,
            content: content
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                // 화면에 블록 추가
                let innerContent = res.content;
                // 만약 이미지 타입이면 태그로 감싸서 보여줌
                if (type === 'Image') {
                    innerContent = `<img src="${res.content}" alt="Image">`;
                }

                let html = `
                    <div class="content-block type-${type}" id="block-${res.id}">
                        <div class="block-content">${innerContent}</div>
                        <button class="btn-del-block" onclick="deleteBlock(${res.id})">×</button>
                    </div>`;
                
                $('#block-stream').append(html);
                if(type !== 'Image') $('#new-block-text').val(''); // 텍스트 입력창만 비움
            } else {
                alert('저장 실패: ' + res.message);
            }
        }
    });
}

// 블록 삭제 함수
function deleteBlock(id) {
    if (!confirm('삭제하시겠습니까?')) return;
    $.ajax({
        url: 'ajax_delete_block.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(res) {
            if (res.success) $('#block-' + id).fadeOut(300, function(){ $(this).remove(); });
        }
    });
}
// 게시글 삭제는 기존 코드 유지
</script>