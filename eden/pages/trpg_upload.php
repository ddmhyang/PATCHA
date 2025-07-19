<?php
// /pages/trpg_upload.php

if (!$is_admin) {
    echo "권한이 없습니다.";
    exit;
}
$gallery_type = 'trpg'; 
?>
<div class="form-page-container">
    <a class="gallery_form_title">새 TRPG 세션 기록</a>
    <form action="trpg_save.php" method="post">
        <input
            type="hidden"
            name="gallery_type"
            value="<?php echo htmlspecialchars($gallery_type); ?>">
        <div class="form-group">
            <label for="title">세션 제목</label>
            <input type="text" id="title" name="title" required="required">
        </div>
        <div class="form-group">
            <label for="thumbnail_file">썸네일 이미지 (선택 사항)</label>
            <input type="file" id="thumbnail_file" name="thumbnail_file" accept="image/*">
            <input type="hidden" id="thumbnail" name="thumbnail" value="">
        </div>
        <div class="form-group">
            <label for="content">세션 내용</label>
            <textarea id="summernote" name="content"></textarea>
        </div>
        <button type="submit">저장하기</button>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    </form>
</div>

<style>

    .form-page-container {
        width: 1170px;
        margin-left: 40px;
    }

    .form-page-container h1 {
        text-align: center;
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 40px;
        margin-top: 45px;
        margin-bottom: 15px;
    }

    .gallery_form_title {
        display: block;
        text-align: center;
        color: rgb(255, 255, 255);
        font-family: 'Fre7';
        font-size: 40px;
        margin-top: 45px;
        margin-bottom: 15px;
    }

    .form-group {
        margin-bottom: 30px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 24px;
        font-family: 'Fre7';
        color: rgba(255, 255, 255, 0.9);
    }

    .form-group input[type="text"],
    .form-group textarea {
        width: calc(100% - 20px);
        padding: 12px;
        border-radius: 5px;
        border: 1px solid rgba(198, 196, 196, 0.3);
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        font-family: 'Fre3';
        font-size: 20px;
        transition: border-color 0.3s ease;
    }

    .note-editor.note-frame {
        background-color: rgba(0, 0, 0, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 5px;
    }
    .note-editor .note-editing-area .note-editable {
        background-color: rgba(0, 0, 0, 0.4);
        color: white;
        min-height: 300px;
        padding: 15px;
    }

    .note-toolbar {
        background-color: rgba(0, 0, 0, 0.7);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }
    .note-btn {
        background-color: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-color: transparent !important;
    }
    .note-btn:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }
    .note-statusbar {
        background-color: rgba(0, 0, 0, 0.7);
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
    }

    button[type="submit"] {
        display: inline-block;
        padding: 12px 25px;
        background-color: rgb(255, 255, 255);
        color: black;
        border: none;
        border-radius: 5px;
        font-size: 20px;
        cursor: pointer;
        margin-right: 10px;
        font-family: 'Fre9';
        margin-bottom: 38px;
    }

    button[type="submit"]:hover {
        background-color: #e9e9e9;
    }

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
        overflow-y: auto;
        overflow-x: hidden;
    }

    .content::-webkit-scrollbar {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background-color: #555;
        border-radius: 4px;
    }
    .content::-webkit-scrollbar-track {
        background-color: #333;
    }
    @media (max-width: 768px) {
        .content {
            position: absolute !important;
            top: 273px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 900px;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.80) 0%, rgba(255, 255, 255, 0.35) 100%);
            padding: 0;
            box-sizing: border-box;
        }
        .form-page-container {
            width: 520px;
            margin-left: 40px;
        }
    }
</style>

<script>
$(document).ready(function () {
    // Summernote 에디터 초기화
    $('#summernote').summernote({
        height: 350,
        callbacks: {
            onImageUpload: function (files) {
                // 한 번에 하나의 파일만 처리하도록 하여 안정성 확보
                if (files.length > 0) {
                    uploadFile(files[0], $(this));
                }
            }
        }
    });

    // 파일 업로드 및 본문 삽입을 위한 단일 통합 함수
    function uploadFile(file, editor) {
        let data = new FormData();
        data.append("file", file);

        let loadingNode = null;
        if (file.type === 'application/pdf') {
             loadingNode = $('<p><em>PDF를 고화질 이미지로 변환 중입니다. 페이지 수에 따라 시간이 걸릴 수 있습니다...</em></p>')[0];
             editor.summernote('insertNode', loadingNode);
        }

        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                if(loadingNode) $(loadingNode).remove();

                if (response.success && response.urls && response.urls.length > 0) {
                    // ▼▼▼▼▼ 여기가 최종 해결책입니다. PDF.js 관련 코드를 모두 제거합니다. ▼▼▼▼▼
                    let imagesHtml = '';
                    // 1. 서버가 보내준 모든 이미지 URL을 <p>와 <img> 태그로 감싸 하나의 HTML 덩어리로 만듭니다.
                    response.urls.forEach(function(url) {
                        imagesHtml += '<p><img src="' + url + '" style="max-width:100%;"></p>';
                    });

                    // 2. 에디터에 포커스를 맞추고, 완성된 HTML 덩어리를 한번에 삽입합니다.
                    editor.summernote('focus');
                    editor.summernote('pasteHTML', imagesHtml);
                    // ▲▲▲▲▲ 여기까지가 최종 코드입니다. ▲▲▲▲▲

                } else {
                    alert('업로드 실패: ' + (response.error || '알 수 없는 오류가 발생했습니다.'));
                }
            },
            error: function (jqXHR) {
                if(loadingNode) $(loadingNode).remove();
                console.error("Upload failed:", jqXHR.responseText);
                alert("파일 업로드 중 서버 오류가 발생했습니다. 개발자 도구를 확인해주세요.");
            }
        });
    }

    // 폼 저장 로직 (기존과 동일하게 유지)
    $('form[action$="_save.php"], form[id="edit-form"]').on('submit', function (e) {
        e.preventDefault();
        if ($(this).find('#summernote').length) {
             $('textarea[name="content"]').val($('#summernote').summernote('code'));
        }
        var formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success && response.redirect_url) {
                    window.location.hash = response.redirect_url;
                } else {
                    alert('저장 실패: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: function () {
                alert('저장 요청 처리 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>