<?php
// /pages/timeline.php
if (!isset($mysqli)) {
    $page_name = basename($_SERVER['PHP_SELF'], '.php');
    header("Location: main.php?page=" . $page_name);
    exit;
}


function get_title_from_html($html) {
    if (empty($html)) { return '제목 없음'; }
    $text = strip_tags($html);
    $lines = preg_split('/\\r\\n|\\r|\\n/', $text);
    foreach ($lines as $line) {
        if (trim($line) !== '') {
            return trim($line);
        }
    }
    return '제목 없음';
}


$stmt = $mysqli->prepare("SELECT id, year, full_description, thumbnail FROM eden_timeline ORDER BY sort_order ASC");
$stmt->execute();
$timeline_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$total_width = (count($timeline_items) * 259) + 200; 
?>

<style>

    .content {
        position: absolute !important;
        top: 220px;
        left: 50%;
        transform: translateX(-50%);
        width: 1250px;
        height: 605px;
        background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
        padding: 0;
        box-sizing: border-box;
    }

    .admin-controls {
        position: absolute;
        top: 28px;
        right: 28px;
        z-index: 10;
        display: flex;
        gap: 5px;
    }

    .admin-controls button {
        background: white;
        border: none;
        padding: 0;
        cursor: pointer;
        width: 28px;
        height: 28px;
        font-size: 18px;
        line-height: 28px;
        text-align: center;
        border-radius: 10px;
    }

    .timeline-wrapper {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        height: 100%;
        width: 100%;
        padding: 0 95px;
        box-sizing: border-box;
        overflow-x: scroll;
        overflow-y: hidden;
    }

    .timeline-wrapper::-webkit-scrollbar {
        display: none;
    }

    .timeline-line {
        position: absolute;
        width: 100%;
        height: 1px;
        background: white;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
    }

    .timeline-events {
        display: flex;
        justify-content: space-around;
        height: 100%;
        position: relative;
        z-index: 2;
    }

    .timeline-item {
        display: flex;
        align-items: center;
        position: relative;
    }

    .timeline-item.top {
        margin-bottom: auto;
        margin-top: 110px;
    }

    .timeline-item.bottom {
        margin-top: auto;
        margin-bottom: 110px;
    }

    .item-container {
        display: flex;
        cursor: pointer;
        text-decoration: none;
        color: white;
    }

    .item-thumbnail {
        width: 145px;
        height: 145px;
        flex-shrink: 0;
        border-radius: 15px;
        background-color: white;

        background-size: cover;
        background-position: center;
    }

    .item-text {
        color: white;
        margin-left: 20px;
        margin-right: 50px;
    }
    .item-text .timeline_year {
        font-size: 20px;
        font-family: 'fre9';
    }
    .item-text .timeline_title {
        font-family: 'fre1';
        font-size: 14px;

        max-width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 0 auto;
    }

    .timeline-item.ui-sortable-helper {
        cursor: grabbing;
        opacity: 0.7;
    }

    #add-view {
        padding: 20px 30px;
        height: 100%;
        box-sizing: border-box;
        overflow-y: auto;
        color: white;
    }

    #add-view form input[type="text"],
    #add-view form textarea {
        width: 100%;
        margin-bottom: 15px;
        padding: 8px;
        box-sizing: border-box;
        background: #333;
        color: white;
        border: 1px solid #555;
    }

    #add-view form label {
        display: block;
        margin-bottom: 5px;
        font-family: 'fre9';
    }
</style>

<div id="timeline-view">
    <?php if ($is_admin): ?>
    <div class="admin-controls">
        <button id="add-btn">+</button>
        <button
            id="save-order-btn"
            style="display:none; font-size: 16px; width:auto; height:auto;">순서 저장</button>
    </div>
    <?php endif; ?>

    <div class="timeline-line"></div>

    <div class="timeline-wrapper">
        <div class="timeline-events" id="timeline-sortable">
            <?php foreach ($timeline_items as $index => $item): ?>
            <div
                class="timeline-item <?php echo ($index % 2 == 0) ? 'top' : 'bottom'; ?>"
                data-id="<?php echo $item['id']; ?>">
                <div class="item-container" data-id="<?php echo $item['id']; ?>">
                    <div
                        class="item-thumbnail"
                        style="background-image: url('<?php echo htmlspecialchars($item['thumbnail'] ?? ''); ?>');"></div>
                    <div class="item-text">
                        <div class="timeline_year">
                            <?php echo htmlspecialchars($item['year']); ?>
                        </div>
                        <div class="timeline_title">
                            <?php echo htmlspecialchars(get_title_from_html($item['full_description'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="detail-view" style="display: none;"></div>

<?php if ($is_admin): ?>
<div id="add-view" style="display: none;">
    <h1 style="color:white;">새 스토리 추가</h1>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <form id="add-timeline-form" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_full_item">
        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo $_SESSION['csrf_token']; ?>">

        <label>제목:</label>
        <input type="text" name="year" required="required">

        <label>썸네일 이미지:</label>
        <input
            type="file"
            name="thumbnail_file"
            accept="image/*"
            style="background:transparent; border:none;">

        <label style="margin-top:15px;">상세 설명:</label>
        <textarea id="add-summernote" name="full_description"></textarea>

        <div style="margin-top:20px;">
            <button
                type="submit"
                style="font-family:Fre7; background:rgb(255, 255, 255); color:black; border:1px; borde-radius 10px; padding: 5px 10px; cursor:pointer; margin-top:5px;">추가하기</button>
            <button
                type="button"
                id="add-cancel-btn"
                style="font-family:Fre7; background:rgb(0, 0, 0); color:white; border:none; padding: 5px 10px; cursor:pointer; margin-top:5px;">취소</button>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
    $(document).ready(function () {

        let summernoteInstances = {
            detail: false,
            add: false
        };

        $('#timeline-sortable').on('click', '.item-container', function () {
            if ($('.timeline-wrapper').hasClass('delete-mode')) 
                return;
            const itemId = $(this).data('id');
            $.ajax({
                url: 'ajax_timeline_handler.php',
                type: 'POST',
                data: {
                    action: 'get_detail',
                    id: itemId
                },
                success: function (responseHtml) {
                    $('#timeline-view').hide();
                    $('#detail-view')
                        .html(responseHtml)
                        .show();

                    if ($('#detail-summernote').length && !summernoteInstances.detail) {
                        $('#detail-summernote').summernote({
                            height: 450,
                            dialogsInBody: true,
                            callbacks: {
                                onImageUpload: function (files) {
                                    uploadImage(files[0], $(this));
                                }
                            }
                        });
                        summernoteInstances.detail = true;
                    }
                }
            });
        });

        function goBackToList() {
            if (summernoteInstances.detail) {
                $('#detail-summernote').summernote('destroy');
                summernoteInstances.detail = false;
            }
            if (summernoteInstances.add) {
                $('#add-summernote').summernote('destroy');
                summernoteInstances.add = false;
            }
            $('#detail-view')
                .hide()
                .html('');
            $('#add-view').hide();
            $('#timeline-view').show();
        }
        $('.content').on('click', '#back-to-timeline-btn', goBackToList);
        $('.content').on('click', '#add-cancel-btn', goBackToList);

        <?php if ($is_admin): ?>

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

        $("#timeline-sortable")
            .sortable({
                axis: "x",
                placeholder: "ui-state-highlight",
                update: function (event, ui) {
                    $(this)
                        .children()
                        .each(function (index) {
                            $(this)
                                .removeClass('top bottom')
                                .addClass(
                                    index % 2 == 0
                                        ? 'top'
                                        : 'bottom'
                                );
                        });
                    $("#save-order-btn").fadeIn();
                }
            })
            .disableSelection();

        $("#save-order-btn").click(function () {
            let order = [];
            $("#timeline-sortable .timeline-item").each(function (index) {
                order.push({
                    id: $(this).data('id'),
                    sort_order: (index + 1) * 10
                });
            });
            $.ajax({
                url: 'ajax_timeline_handler.php',
                type: 'POST',
                data: {
                    action: 'reorder',
                    order: order,
                    csrf_token: '<?php echo $csrf_token; ?>'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('순서가 저장되었습니다.');
                        $("#save-order-btn").fadeOut();
                    } else {
                        alert('순서 저장 실패: ' + response.message);
                    }
                }
            });
        });

        $("#add-btn").click(function () {
            $('#timeline-view').hide();
            $('#add-view').show();
            if ($('#add-summernote').length && !summernoteInstances.add) {
                $('#add-summernote').summernote({
                    height: 350,
                    dialogsInBody: true,
                    callbacks: {
                        onImageUpload: function (files) {
                            uploadImage(files[0], $(this));
                        }
                    }
                });
                summernoteInstances.add = true;
            }
        });

        $('.content').on('submit', '#add-timeline-form', function (e) {
            e.preventDefault();
            $('textarea[name="full_description"]').val(
                $('#add-summernote').summernote('code')
            );
            let formData = new FormData(this);
            $.ajax({
                url: 'ajax_timeline_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('새로운 스토리가 추가되었습니다.');
                        location.reload();
                    } else {
                        alert('추가 실패: ' + response.message);
                    }
                }
            });
        });

        $('.content').on('submit', '#detail-edit-form', function (e) {
            e.preventDefault();
            $('textarea[name="full_description"]').val(
                $('#detail-summernote').summernote('code')
            );
            let formData = new FormData(this);
            $.ajax({
                url: 'ajax_timeline_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('저장되었습니다.');
                        location.reload();
                    } else {
                        alert('저장 실패: ' + response.message);
                    }
                }
            });
        });

        $('.content').on('click', '#detail-edit-btn', function () {
            $('#detail-view-mode').hide();
            $('#detail-edit-mode').show();
        });
        $('.content').on('click', '#detail-cancel-btn', function () {
            $('#detail-edit-mode').hide();
            $('#detail-view-mode').show();
        });
        $('.content').on('click', '#detail-delete-btn', function () {
            if (!confirm('정말 삭제하시겠습니까?')) 
                return;
            const itemId = $(this).data('id');
            $.ajax({
                url: 'ajax_timeline_handler.php',
                type: 'POST',
                data: {
                    action: 'delete_item',
                    id: itemId,
                    csrf_token: '<?php echo $csrf_token; ?>'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('삭제되었습니다.');
                        location.reload();
                    } else {
                        alert('삭제 실패: ' + response.message);
                    }
                }
            });
        });

        <?php endif; ?>
    });
</script>