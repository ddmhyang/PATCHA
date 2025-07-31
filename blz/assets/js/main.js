function adjustScale() {
    const container = $('.container');
    if (!container.length) 
        return;
    const windowWidth = window.innerWidth,
        windowHeight = window.innerHeight;
    const containerWidth = 1440,
        containerHeight = 900;
    const scale = Math.min(
        windowWidth / containerWidth,
        windowHeight / containerHeight
    );
    container.css({
        'transform': `scale(${scale})`,
        'left': `${ (windowWidth - containerWidth * scale) / 2}px`,
        'top': `${ (windowHeight - containerHeight * scale) / 2}px`
    });
}

$(document).ready(function () {
    
    $.ajaxSetup({cache: false});

    adjustScale();
    $('body').css('visibility', 'visible');

    const content = $('#content');

    
    function loadPage(page, params = '') {
        content.load(page + '.php?' + params, function (status, response) {
            if (status === "error") {
                content.html("<h2>페이지를 찾을 수 없습니다.</h2>");
            } else {
                if (isAdmin && $('.summernote, #summernote-post').length) {
                    initializeSummernote();
                }
                updateActiveNav(page, params);
            }
        });
    }

    function initializeSummernote() {
        $('.summernote, #summernote-post').summernote({
            height: 800,
            callbacks: {
                onImageUpload: function (files) {
                    uploadFile(files[0], $(this));
                }
            }
        });
    }

    function uploadFile(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: (response) => {
                if (response.success && response.urls) {
                    response
                        .urls
                        .forEach(url => editor.summernote('insertImage', url));
                } else {
                    alert('파일 업로드 실패: ' + (
                        response.error || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('업로드 중 서버 오류 발생')
        });
    }

    function updateActiveNav(page, params) {
        let activePage = page;
        if (['post_view', 'post_upload', 'post_edit'].includes(page)) {
            if (params) {
                const urlParams = new URLSearchParams(params);
                if (urlParams.has('type')) {
                    activePage = urlParams.get('type');
                }
            }
            if (activePage === 'post_view') {
                const backLink = $('#content')
                    .find('.btn-back-to-list')
                    .attr('href');
                if (backLink) {
                    activePage = backLink.substring(2);
                }
            }
        }
        $('.nav_btn').removeClass('active');
        $(`.nav_btn[data-page="${activePage}"]`).addClass('active');
    }

    function router() {
        const hash = window
            .location
            .hash
            .substring(2) || 'main';
        const [page, params] = hash.split('?');
        loadPage(page, params);
    }

    
    $('header nav').on('click', '.nav_btn', function (e) {
        if ($(this).parent('a').length) 
            return;
        e.preventDefault();
        window.location.hash = `#/${$(this).data('page')}`;
    });

    content.on('click', '.edit-btn', function () {
        $(this)
            .closest('.view-mode')
            .hide()
            .siblings('.edit-mode')
            .show();
    });

    content.on('click', '.cancel-btn', function () {
        $(this)
            .closest('.edit-mode')
            .hide()
            .siblings('.view-mode')
            .show();
    });

    content.on('click', '.save-btn', function () {
        const form = $(this).closest('form');
        const slug = form
            .closest('.page-content')
            .data('slug');
        const contentHtml = form
            .find('.summernote')
            .summernote('code');
        $.post('ajax_save_page.php', {
            slug: slug,
            content: contentHtml
        }, (response) => {
            if (response.success) {
                alert('저장되었습니다.');
                const container = form.closest('.page-content');
                container
                    .find('.content-display')
                    .html(contentHtml);
                container
                    .find('.edit-mode')
                    .hide()
                    .siblings('.view-mode')
                    .show();
            } else {
                alert('저장 실패: ' + response.message);
            }
        }, 'json');
    });

    content.on('submit', '#post-form', function (e) {
        e.preventDefault();
        const form = $(this);
        form
            .find('textarea[name="content"]')
            .val(form.find('#summernote-post').summernote('code'));
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: (response) => {
                if (response.success) {
                    alert('게시물이 저장되었습니다.');
                    window.location.hash = `#/post_view?id=${response.redirect_id}`;
                } else {
                    alert('저장 실패: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('저장 중 서버 오류가 발생했습니다.')
        });
    });

    content.on('click', '.delete-post-btn', function () {
        if (!confirm('정말 삭제하시겠습니까?')) 
            return;
        const postId = $(this).data('id');
        const postType = $(this).data('type');
        $.post('ajax_delete_post.php', {
            id: postId
        }, (response) => {
            if (response.success) {
                alert('삭제되었습니다.');
                window.location.hash = `#/${postType}`;
            } else {
                alert('삭제 실패: ' + response.message);
            }
        }, 'json');
    });

    content.on('change', '#thumbnail_file', function () {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('thumbnail_file', this.files[0]);
            $.ajax({
                url: 'ajax_upload_thumbnail.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#thumbnail_path').val(response.url);
                        $('#thumbnail-preview').html(`<img src="${response.url}" alt="미리보기">`);
                    } else {
                        alert('썸네일 업로드 실패: ' + response.message);
                    }
                }
            });
        }
    });

    content.on('submit', '#login-form', function (e) {
        e.preventDefault();
        $('#login-error').text('');
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = 'index.php';
                } else {
                    $('#login-error').text(response.message);
                }
            },
            error: () => $('#login-error').text('로그인 중 서버 오류가 발생했습니다.')
        });
    });

    
    router();
    $(window).on('hashchange', router);
    $(window).on('resize', adjustScale);
});