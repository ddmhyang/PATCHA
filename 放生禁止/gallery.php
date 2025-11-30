<?php 
require_once 'includes/db.php'; 

$sort = isset($_GET['sort']) && strtoupper($_GET['sort']) === 'ASC' ? 'ASC' : 'DESC';
$next_sort = ($sort === 'ASC') ? 'desc' : 'asc';

$gallery_type = 'gallery';

$sql = "SELECT id, title, created_at, subtitle FROM gallery WHERE gallery_type = '$gallery_type' ORDER BY created_at $sort";
$posts = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<div class="gallery-container">
    <a href="#/gallery?sort=<?php echo $next_sort; ?>" class="sort-btn"></a>
    
    <?php if ($is_admin): ?>
        <a href="javascript:void(0);" onclick="createNewPost('<?php echo $gallery_type; ?>')" class="add-btn"></a>
        <button id="delete-mode-btn" class="del-mode-btn"></button>
    <?php endif; ?>

    <div class="card-grid">
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                
                <a href="#/gallery_view?id=<?php echo $post['id']; ?>" class="card-item">
                    <?php if ($is_admin): ?>
                        <div class="del-check-wrapper">
                            <input type="checkbox" class="del-checkbox" value="<?php echo $post['id']; ?>">
                        </div>
                    <?php endif; ?>

                    <div class="card-top">
                        <span class="card-title"><?php echo htmlspecialchars($post['title']); ?></span>
                    </div>

                    <div class="card-date">
                        <?php echo htmlspecialchars($post['subtitle']); ?>
                    </div>
                </a>

            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1; text-align:center; padding: 50px; color: #999;">등록된 페이지가 없습니다.</p>
        <?php endif; ?>
    </div>
</div>

<script>
var isDeleteMode = false;

$('#delete-mode-btn').off('click').on('click', function() {
    if (!isDeleteMode) {
        isDeleteMode = true;
        $('.gallery-container').addClass('delete-mode');
        $(this).addClass('active');
    } else {
        executeMultiDelete();
    }
});

$('.del-checkbox').on('click', function(e) {
    e.stopPropagation();
});

function executeMultiDelete() {
    var selectedIds = [];
    $('.del-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) {
        alert('삭제할 항목을 선택해주세요. (취소하려면 F5)');
        return;
    }

    if (!confirm(selectedIds.length + '개의 페이지를 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: 'ajax_delete_gallery_multi.php',
        type: 'POST',
        data: { ids: selectedIds },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert('삭제되었습니다.');
                location.reload();
            } else {
                alert('삭제 실패: ' + res.message);
            }
        },
        error: function() {
            alert('서버 통신 오류');
        }
    });
}

function createNewPost(type) {
    $.ajax({
        url: 'ajax_create_gallery.php',
        type: 'POST',
        data: { gallery_type: type },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                location.href = '#/gallery_view?id=' + res.id;
            } else {
                alert('글 생성 실패: ' + res.message);
            }
        },
        error: function() {
            alert('서버 통신 오류 (글 생성)');
        }
    });
}
</script>