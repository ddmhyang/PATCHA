// --- 파일 경로: /assets/js/main.js (전체 교체) ---
$(document).ready(function () {
    const contentContainer = $('#content-container');

    // 1. 페이지 로드 함수
    function loadPage(url) {
        // api.php를 통해 페이지 콘텐츠를 비동기적으로 요청
        $.ajax({
            url: `api.php?${url}`,
            type: 'GET',
            success: function (response) {
                contentContainer.html(response);
                updateActiveLink(url);
            },
            error: function () {
                contentContainer.html('<h2>페이지를 불러오는 데 실패했습니다.</h2>');
            }
        });
    }

    // 2. 현재 페이지에 맞춰 메뉴 활성화
    function updateActiveLink(url) {
        const page = new URLSearchParams(url).get('page');
        $('nav > a').removeClass('active');
        $(`nav > a[data-page="${page}"]`).addClass('active');
    }

    // 3. 라우터: 주소창의 # 값 변경을 감지하여 페이지 로드
    function router() {
        const path = window.location.hash.substring(2) || 'main_content';
        const [page, queryString] = path.split('?');
        
        const allowed_pages = [
             'main_content', 'dolfolil', 'adolfo', 'lilian', 'messenger',
             'gallery', 'gallery_view', 'gallery_upload', 'gallery_edit',
             'trpg', 'trpg_view', 'trpg_upload', 'trpg_edit'
        ];
        
        if (allowed_pages.includes(page)) {
            loadPage(`page=${page}${queryString ? '&' + queryString : ''}`);
        } else {
            window.location.hash = '#/main_content';
        }
    }

    // --- 이벤트 핸들러 ---

    // a 태그 클릭 시 SPA 라우팅 처리
    $(document).on('click', 'a', function (e) {
        const href = $(this).attr('href');
        if (href && href.startsWith('#/')) {
            e.preventDefault();
            window.location.hash = href;
        }
    });

    // 폼 제출(글쓰기/수정) 처리
    $(document).on('submit', 'form', function (e) {
        e.preventDefault();
        
        // Summernote 내용 업데이트
        if ($(this).find('.note-editor').length) {
            $(this).find('textarea[name="content"]').val($(this).find('.summernote').summernote('code'));
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
                if (response.success) {
                    alert('성공적으로 처리되었습니다.');
                    if (response.redirect_url) {
                        window.location.hash = response.redirect_url;
                    }
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function () {
                alert('요청 처리 중 오류가 발생했습니다.');
            }
        });
    });

    // 삭제 버튼 처리
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        if (!confirm('정말 삭제하시겠습니까?')) return;
        
        var postId = $(this).data('id');
        var deleteUrl = $(this).data('url');
        
        $.ajax({
            url: deleteUrl,
            type: 'POST',
            data: { id: postId, token: csrfToken },
            dataType: 'json',
            success: function (response) {
                if (response.success && response.redirect_url) {
                    alert('삭제되었습니다.');
                    window.location.hash = response.redirect_url;
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
            error: function () {
                alert('삭제 요청 처리 중 오류가 발생했습니다.');
            }
        });
    });

    // --- 초기 실행 ---
    $(window).on('hashchange', router).trigger('hashchange');

    function adjustScale() {
        const container = document.querySelector('.container');
        if (!container) 
            return;
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        if (windowWidth <= 784) {
            containerWidth = 720;
            containerHeight = 1280;
        } else {
            containerWidth = 1440;
            containerHeight = 900;
        }

        const scale = Math.min(
            windowWidth / containerWidth,
            windowHeight / containerHeight
        );
        container.style.transform = `scale(${scale})`;
        container.style.left = `${ (windowWidth - containerWidth * scale) / 2}px`;
        container.style.top = `${ (windowHeight - containerHeight * scale) / 2}px`;
    }
    window.addEventListener('load', () => {
        adjustScale();
        document.body.style.visibility = 'visible';
    });
    window.addEventListener('resize', adjustScale);
});
