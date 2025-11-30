<?php
require_once 'includes/db.php';

$post_id = intval($_GET['id'] ?? 0);
if ($post_id <= 0) die("잘못된 접근입니다.");

$stmt = $mysqli->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) die("게시글이 없습니다.");

$blocks_sql = "SELECT * FROM post_blocks WHERE gallery_id = $post_id ORDER BY id ASC";
$blocks = $mysqli->query($blocks_sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="view-container">
    
    <div class="header-area">
        <?php if ($is_admin): ?>
            <div style="font-size: 50px;" id="post-title" class="editable-title" contenteditable="true" spellcheck="false"><?php echo htmlspecialchars($post['title']); ?></div>
            
            <div class="admin-meta-controls">
                <a href="#/gallery" class="btn-mini btn-back">목록</a>
                <button class="btn-mini btn-delete-post" data-id="<?php echo $post_id; ?>">삭제</button>
            </div>
        <?php else: ?>
            <div style="font-size: 50px;" class="editable-title"><?php echo htmlspecialchars($post['title']); ?></div>
            <div class="post-actions-top">
                <a href="#/gallery" class="btn-back-to-list">목록</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="subtitle-area">
        <?php if ($is_admin): ?>
            <div id="post-subtitle" style="font-size: 24px;" class="editable-subtitle" contenteditable="true" spellcheck="false"><?php echo htmlspecialchars($post['subtitle'] ?? ''); ?></div>
        <?php else: ?>
            <?php if (!empty($post['subtitle'])): ?>
                <div class="editable-subtitle readonly" style="font-size: 24px;"><?php echo htmlspecialchars($post['subtitle']); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>


    <?php if ($is_admin): ?>
    <button onclick="addBlock('B')" class="btn-ctrl btn-a" id="btn-ctrl-blue">
        <i class="fa-solid fa-paper-plane" style="color:#337EA9"></i>
        &nbsp;&nbsp;전송  
    </button>
    <button onclick="addBlock('A')" class="btn-ctrl btn-b" id="btn-ctrl-orange">
        <i class="fa-solid fa-paper-plane" style="color:#D9730D"></i>    
        &nbsp;&nbsp;전송  
    </button>
    <?php endif; ?>

    <div id="content-canvas" class="<?php echo $is_admin ? 'admin-mode' : ''; ?>">
        <?php foreach ($blocks as $block): ?>
            <div class="content-block type-<?php echo $block['block_type']; ?>" id="block-<?php echo $block['id']; ?>" data-id="<?php echo $block['id']; ?>">
                
                <div class="block-text" <?php echo $is_admin ? 'contenteditable="true"' : ''; ?>><?php echo $block['content']; ?></div>

                <?php if ($is_admin): ?>
                    <div class="block-tools">
                        <button class="tool-btn btn-add-img" title="이미지 추가" onclick="triggerBlockImageUpload(<?php echo $block['id']; ?>)">+</button>
                        <button class="tool-btn btn-del" title="삭제" onclick="deleteBlock(<?php echo $block['id']; ?>)">x</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<style>

<style > .view-container {
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 120px;
    position: relative;
}

.header-area {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 10px;
}

.editable-title {
    flex-grow: 1;
    font-size: 28px;
    font-weight: bold;
    color: #333;
    outline: none;
    padding: 5px 10px;
    border-radius: 5px;
    border: 1px solid transparent;
    transition: all 0.2s;
    cursor: text;
    margin: 0;
    line-height: 1.2;
}

.admin-mode .editable-title:hover,
.editable-title:focus {
    border-bottom: 2px solid #595959;
}
.admin-meta-controls {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
    margin-top: 5px;
}

.btn-mini {
    border: 1px solid #d4d4d4;
    background: #d4d4d4;
    padding: 12px 20px;
    border-radius: 15px;
    cursor: pointer;
    font-size: 16px;
    color: #595959;
    font-family: 'fre9';
    text-decoration: none;
    display: inline-block;
    transition: transform 0.2s ease-in-out;
}
.btn-mini:hover {
    transform: scale(1.05);
}
.btn-delete-post {
    color: #d4d4d4;
    background: #595959;
    border: #595959 2px solid;
}
.btn-back {
    color: #595959;
    background: #d4d4d4;
    border: #595959 2px solid;
    margin-right: 10px;
}

.content-block {
    position: relative;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 8px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.block-text {
    outline: none;
    white-space: pre-wrap;
    line-height: 1.6;
    font-family: 'Fre3';
    font-size: 20px;
    min-height: 1.2em;
    padding: 2px 5px;
}

.block-text:empty:before {
    content: '내용을 입력하세요';
    color: #ccc;
    font-size: 14px;
}

.block-text img {
    max-height: 150px;
    width: auto;
    border-radius: 5px;
    display: block;
    margin: 10px 0;
    cursor: default;
}

.type-A {
    background: #F9F3DC;
    color: #595959;
}
.type-B {
    background: #E5F2FC;
    color: #595959;
}

.block-tools {
    position: absolute;
    top: -15px;
    right: 10px;
    display: none;
    gap: 5px;
    padding: 3px 8px;
    z-index: 10;
}

.content-block:focus-within .block-tools,
.content-block:hover .block-tools {
    display: flex;
}

.tool-btn {
    border: none;
    background: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    padding: 0 5px;
}
.btn-add-img {
    color: #595959;
    background: #d4d4d4;
}
.btn-del {
    color: #d4d4d4;
    background: #595959;
}

.btn-ctrl {
    padding: 16px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-family: 'Fre9';
    font-size: 18px;
    margin: 20px 0 40px;
}
.btn-a {
    background: #D4D4D4;
    border: 2px solid #595959;
    color: #595959;
}
.btn-b {
    background: #D4D4D4;
    border: 2px solid #595959;
    color: #595959;
    margin-left: 350px;
}

.subtitle-area {
    margin-top: 10px;
    margin-bottom: 20px;
    margin-left: 10px;
}

.editable-subtitle {
    font-family: 'Fre3';
    font-size: 18px;
    color: #595959;
    line-height: 1.5;
    padding: 2px 0 2px 15px;
    border-left: 4px solid #595959;
    outline: none;
    min-height: 20px;
    transition: background-color 0.2s;
}

.admin-mode .editable-subtitle:hover,
.editable-subtitle:focus {
    border-bottom: 2px solid #595959;
}

.editable-subtitle:empty:before {
    content: '소제목을 입력하세요';
    color: #595959;
}

</style>

<script>
<?php if ($is_admin): ?>
var currentUploadBlockId = 0;

window.addBlock = function(type) {
    $.ajax({
        url: 'ajax_add_block_to_post.php',
        type: 'POST',
        data: { gallery_id: <?php echo $post_id; ?>, type: type, content: '' },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                let html = `
                    <div class="content-block type-${type}" id="block-${res.id}" data-id="${res.id}">
                        <div class="block-text" contenteditable="true"></div>
                        <div class="block-tools">
                            <button class="tool-btn btn-add-img" onclick="triggerBlockImageUpload(${res.id})">+</button>
                            <button class="tool-btn btn-del" onclick="deleteBlock(${res.id})">x</button>
                        </div>
                    </div>`;
                $('#content-canvas').append(html);
                $('#block-' + res.id).find('.block-text').focus();
            } else {
                alert('블록 추가 실패: ' + (res.message || '오류 발생'));
            }
        },
        error: function() { alert('서버 통신 오류 (블록 추가)'); }
    });
};

$(document).off('focusout', '.block-text').on('focusout', '.block-text', function() {
    let $block = $(this).closest('.content-block');
    let id = $block.data('id');
    let content = $(this).html(); 

    if (content.trim() === '<br>') content = '';

    $.ajax({
        url: 'ajax_update_block.php',
        type: 'POST',
        data: { id: id, content: content },
        dataType: 'json',
        success: function(res) {
            if (res.success) console.log('블록 저장 완료');
            else console.error('블록 저장 실패');
        }
    });
});

window.triggerBlockImageUpload = function(blockId) {
    currentUploadBlockId = blockId; 
    $('#hidden-block-img-input').click(); 
};

$('#hidden-block-img-input').off('change').on('change', function() {
    let file = this.files[0];
    if (!file || currentUploadBlockId === 0) return;

    let formData = new FormData();
    formData.append('file', file);

    $.ajax({
        url: 'ajax_upload_image.php',
        type: 'POST',
        data: formData,
        contentType: false, processData: false, dataType: 'json',
        success: function(res) {
            if (res.success) {
                let imgTag = `<br><img src="${res.url}">`;
                let $targetBlock = $('#block-' + currentUploadBlockId).find('.block-text');
                
                $targetBlock.append(imgTag);
                $targetBlock.trigger('focusout');
            } else {
                alert('이미지 업로드 실패: ' + res.message);
            }
        },
        error: function() { alert('서버 통신 오류 (이미지 업로드)'); }
    });
    $(this).val(''); 
});

$('#post-title, #post-subtitle').off('focusout').on('focusout', function() {
    
    let title = $('#post-title').text().trim();
    if(title === '') { title = '제목 없음'; $('#post-title').text(title); }

    let subtitle = $('#post-subtitle').text().trim();

    $.ajax({
        url: 'ajax_save_gallery.php', 
        type: 'POST',
        data: { 
            id: <?php echo $post_id; ?>, 
            title: title, 
            subtitle: subtitle,
            gallery_type: '<?php echo $post['gallery_type']; ?>'
        },
        success: function(res) { 
            if(res.success) console.log('저장 완료');
            else console.error('저장 실패: ' + res.message);
        },
        error: function() {
            alert('서버 통신 오류 (저장)');
        }
    });
});

window.deleteBlock = function(id) {
    if (!confirm('삭제하시겠습니까?')) return;
    $.ajax({
        url: 'ajax_delete_block.php',
        type: 'POST',
        data: { id: id },
        success: function(res) {
            if(res.success) $('#block-' + id).remove();
            else alert('삭제 실패');
        }
    });
};

$('.btn-delete-post').off('click').on('click', function() {
    if (!confirm('글을 완전히 삭제하시겠습니까?')) return;
    $.ajax({
        url: 'ajax_delete_gallery.php',
        type: 'POST',
        data: { id: <?php echo $post_id; ?> },
        success: function(res) {
            window.location.href = '#/gallery';
        }
    });
});
<?php endif; ?>
</script>