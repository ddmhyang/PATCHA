// /assets/js/main.js (최종 수정본)

// --- 1. 스케일 조정 함수 ---
function adjustScale() {
    const container = $('.container');
    if (!container.length) return;
    const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
    const containerWidth = 1440, containerHeight = 900;
    const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
    container.css({
        'transform': `scale(${scale})`,
        'left': `${(windowWidth - containerWidth * scale) / 2}px`,
        'top': `${(windowHeight - containerHeight * scale) / 2}px`
    });
}

// --- 2. 페이지 로딩 완료 후 실행 ---
$(window).on('load', function() {
    adjustScale();
    $('body').css('visibility', 'visible');

    const content = $('#content');

    // --- 페이지 로딩 및 라우팅 로직 ---
    function loadPage(page, params = '') {
        content.load(page + '.php?' + params, function(status, response) {
            if (status === "error") {
                content.html("<h2>페이지를 찾을 수 없습니다.</h2>");
            } else if (isAdmin && $('.summernote, #summernote-post').length) {
                initializeSummernote();
            }
        });
    }

    function initializeSummernote() {
        $('.summernote, #summernote-post').summernote({
            height: 400,
            callbacks: {
                onImageUpload: function(files) {
                    uploadFile(files[0], $(this));
                }
            }
        });
    }

    function uploadFile(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php', type: "POST", data: data,
            contentType: false, processData: false, dataType: 'json',
            success: (response) => {
                if (response.success && response.urls) {
                    response.urls.forEach(url => editor.summernote('insertImage', url));
                } else {
                    alert('파일 업로드 실패: ' + (response.error || '알 수 없는 오류'));
                }
            },
            error: () => alert('업로드 중 서버 오류 발생')
        });
    }

    function router() {
        const hash = window.location.hash.substring(2) || 'main';
        const [page, params] = hash.split('?');
        loadPage(page, params);

        // 활성화된 버튼 스타일 업데이트
        $('.nav_btn').removeClass('active');
        $(`.nav_btn[data-page="${page}"]`).addClass('active');
    }


    // --- 이벤트 핸들러 ---

    // SVG 내비게이션 버튼 클릭 이벤트
    $('header nav').on('click', '.nav_btn', function(e) {
        // a 태그로 감싸진 버튼은 제외 (로그인/로그아웃)
        if ($(this).parent('a').length) return;
        
        e.preventDefault();
        const page = $(this).data('page');
        window.location.hash = `#/${page}`;
    });

    content.on('click', '.edit-btn', function() {
        const container = $(this).closest('.page-content');
        container.find('.view-mode').hide();
        container.find('.edit-mode').show();
    });

    content.on('click', '.cancel-btn', function() {
        const container = $(this).closest('.page-content');
        container.find('.edit-mode').hide();
        container.find('.view-mode').show();
    });

    content.on('click', '.save-btn', function() {
        const form = $(this).closest('form');
        const slug = form.closest('.page-content').data('slug');
        const contentHtml = form.find('.summernote').summernote('code');
        $.post('ajax_save_page.php', { slug: slug, content: contentHtml }, (response) => {
            if (response.success) {
                alert('저장되었습니다.');
                const container = form.closest('.page-content');
                container.find('.content-display').html(contentHtml);
                container.find('.edit-mode').hide();
                container.find('.view-mode').show();
            } else {
                alert('저장 실패: ' + response.message);
            }
        }, 'json');
    });

    // 갤러리 폼 제출 이벤트 핸들러
    content.on('submit', '#post-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const summernoteContent = form.find('#summernote-post').summernote('code');
        form.find('textarea[name="content"]').val(summernoteContent); // summernote 내용을 textarea에 설정

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
                    alert('저장 실패: ' + response.message);
                }
            },
            error: () => alert('저장 중 서버 오류가 발생했습니다.')
        });
    });

    content.on('click', '.delete-post-btn', function() {
        if (!confirm('정말 삭제하시겠습니까?')) return;
        const postId = $(this).data('id');
        const postType = $(this).data('type');
        $.post('ajax_delete_post.php', { id: postId }, (response) => {
            if (response.success) {
                alert('삭제되었습니다.');
                window.location.hash = `#/${postType}`;
            } else {
                alert('삭제 실패: ' + response.message);
            }
        }, 'json');
    });

    // 썸네일 파일 선택 시 바로 업로드
    content.on('change', '#thumbnail_file', function() {
        if (this.files && this.files[0]) {
            const formData = new FormData();
            formData.append('thumbnail_file', this.files[0]);
            $.ajax({
                url: 'ajax_upload_thumbnail.php',
                type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
                success: function(response) {
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
    
    router();
    $(window).on('hashchange', router);
});

// --- 창 크기 변경 이벤트 ---
$(window).on('resize', adjustScale);