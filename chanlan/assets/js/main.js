$(document).ready(function() {
    const contentContainer = $('#content');

    function loadPage(url) {
        $.ajax({
            url: url, type: 'GET',
            success: (response) => contentContainer.html(response),
            error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
        });
    }

    function router() {
        // URL 해시가 비어있으면 'main_content'를 기본값으로 사용
        const hash = window.location.hash.substring(2) || 'main_content';
        const [page, params] = hash.split('?');
        loadPage(`${page}.php${params ? '?' + params : ''}`);
    }
    $(window).on('hashchange', router);

    window.uploadSummernoteImage = function(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php', type: "POST", data: data,
            contentType: false, processData: false, dataType: 'json',
            success: (response) => {
                if (response.success) editor.summernote('insertImage', response.url);
                else alert('이미지 업로드 실패: ' + response.message);
            },
            error: () => alert('이미지 업로드 중 서버 오류 발생')
        });
    };

    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        if (form.find('.summernote').length) {
            formData.set('content', form.find('.summernote').summernote('code'));
        }
        $.ajax({
            url: form.attr('action'), type: 'POST', data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: (response) => {
                if (response.success) {
                    alert('성공적으로 처리되었습니다.');
                    if (response.redirect_url) window.location.hash = response.redirect_url;
                    else router();
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: () => alert('요청 처리 중 오류 발생')
        });
    });

    $(document).on('click', '.delete-btn', function() {
        if (!confirm('정말로 삭제하시겠습니까?')) return;
        const id = $(this).data('id');
        const type = $(this).data('type');
        $.ajax({
            url: 'ajax_delete_gallery.php', type: 'POST', data: { id: id }, dataType: 'json',
            success: (response) => {
                if (response.success) {
                    alert('삭제되었습니다.');
                    window.location.hash = `#/${type}`;
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
            error: () => alert('삭제 요청 중 오류 발생')
        });
    });
});