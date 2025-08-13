$(document).ready(function() {
    const contentContainer = $('#content');

    function loadPage(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                contentContainer.html(response);
            },
            error: function() {
                contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>');
            }
        });
    }

    function router() {
        const hash = window.location.hash.substring(2) || 'main_content';
        const [page, params] = hash.split('?');
        const url = `${page}.php${params ? '?' + params : ''}`;
        loadPage(url);
    }

    $(window).on('hashchange', router);
    router();

    // Summernote 이미지 업로드를 위한 전역 함수 (오류 수정)
    window.uploadSummernoteImage = function(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.url) {
                    // editor.summernote('insertImage', response.url); // 이 방식이 오류를 유발할 수 있음
                    $(editor).summernote('insertImage', response.url); // editor를 jQuery 객체로 감싸서 호출
                } else {
                    alert('이미지 업로드 실패: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    };

    // 공통 AJAX 폼 제출 처리
    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);

        if (form.find('.summernote').length) {
            formData.set('content', form.find('.summernote').summernote('code'));
        }

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('성공적으로 처리되었습니다.');
                    if (response.redirect_url) {
                        window.location.hash = response.redirect_url;
                    } else {
                        router(); // 페이지 새로고침
                    }
                } else {
                    alert('오류: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('요청 처리 중 오류가 발생했습니다.')
        });
    });

    // 삭제 버튼 공통 처리
    $(document).on('click', '.delete-btn', function() {
        if (!confirm('정말로 삭제하시겠습니까?')) return;

        const id = $(this).data('id');
        const type = $(this).data('type');

        $.ajax({
            url: 'ajax_delete_gallery.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('삭제되었습니다.');
                    window.location.hash = `#/${type}`;
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
             error: () => alert('삭제 요청 중 서버 오류가 발생했습니다.')
        });
    });
});