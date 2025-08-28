$(document).ready(function () {
    const isAdmin = $('nav a[href="logout.php"]').length > 0;
    let currentScale = 1;

    // 화면 크기에 맞게 전체 컨테이너의 스케일을 조정하는 함수
    function adjustScale() {
        const container = $('.container');
        if (!container.length) 
            return;
        
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;
        let containerWidth = 1440;
        let containerHeight = 900;

        const scale = Math.min(
            windowWidth / containerWidth,
            windowHeight / containerHeight
        );
        currentScale = scale;

        container.css({
            'width': containerWidth + 'px',
            'height': containerHeight + 'px',
            'transform': `scale(${scale})`,
            'left': `${ (windowWidth - containerWidth * scale) / 2}px`,
            'top': `${ (windowHeight - containerHeight * scale) / 2}px`
        });
        container.show();
    }

    // SPA의 핵심, URL 해시(hash)에 따라 적절한 콘텐츠를 보여주는 라우터 함수
    function router() {
        const hash = window.location.hash || '#/timeline';

        // 페이지 전환 시 모든 view를 숨기고, AJAX 콘텐츠 영역을 비웁니다.
        $('.view').removeClass('active');
        $('#ajax-content-view').empty();

        if (hash.startsWith('#/timeline_view') || hash.startsWith('#/timeline_form')) {
            $('#ajax-content-view').addClass('active');

            // --- 👇 이 부분을 수정하세요 ---
            let url = hash.substring(2); // 예: "timeline_view?id=4"
            const queryIndex = url.indexOf('?');

            if (queryIndex !== -1) {
                // 쿼리 스트링(?id=4)이 있는 경우
                const base = url.substring(0, queryIndex);
                const query = url.substring(queryIndex);
                url = base + '.php' + query; // 결과: "timeline_view.php?id=4"
            } else {
                // 쿼리 스트링이 없는 경우
                url = url + '.php';
            }
            // --- 👆 여기까지 수정 ---

            loadAjaxPage(url);

        } else if (hash.startsWith('#/timeline') || hash.startsWith('#/novel_timeline') || hash.startsWith('#/roleplay_timeline') || hash.startsWith('#/trpg_timeline')) {
            // 타임라인 목록 요청일 경우
            $('#timeline-container').addClass('active');
            loadTimeline(hash);

        } else if (hash === '#/login') {
            // 로그인 요청일 경우
            $('#login-view').addClass('active');

        } else {
            // 그 외 모든 경우 (기본 페이지)
            $('#timeline-container').addClass('active');
            loadTimeline('#/timeline');
        }
    }

    // timeline_view.php, timeline_form.php 등을 AJAX로 로드하는 함수
    function loadAjaxPage(url) {
        $('#ajax-content-view').load(url, function (response, status, xhr) {
            if (status == "error") {
                $(this).html(
                    "<h2>페이지를 불러올 수 없습니다.</h2><p>" + xhr.status + " " + xhr.statusText + "</p>"
                );
            }
            // 폼 페이지를 로드했다면 Summernote 에디터를 활성화합니다.
            if (url.includes('timeline_form.php')) {
                initializeSummernote();
            }
        });
    }

    // 타임라인 목록을 로드하는 함수
    function loadTimeline(hash) {
        let timelineType = 'overall';
        if (hash.includes('_timeline')) {
            timelineType = hash
                .split('_')[0]
                .substring(2);
        }
        // 타임라인 로드 후, 초기화 함수에 timelineType을 인자로 전달합니다.
        $('#timeline-container').load(
            'timeline.php?timeline_type=' + timelineType,
            function () {
                initializeTimeline(timelineType); // timelineType을 넘겨주도록 변경
            }
        );
    }
    // 로그인 폼 제출을 AJAX로 처리하는 함수 form 태그는 index.php에 이미 존재하므로, 페이지 로드 시 바로 이벤트를
    // 바인딩합니다.
    $('#login-form').on('submit', function (event) {
        event.preventDefault(); // 폼의 기본 제출(페이지 새로고침) 동작을 막습니다.

        const formData = new FormData(this);
        const errorElement = $('#login-error');
        errorElement.text(''); // 이전 에러 메시지 초기화

        fetch('login_process.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 로그인 성공 시 페이지를 완전히 새로고침하여 로그인 상태(nav 등)를 반영합니다.
                    window.location.href = 'index.php';
                } else {
                    // 로그인 실패 시 에러 메시지를 표시합니다.
                    errorElement.text(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorElement.text('로그인 중 오류가 발생했습니다.');
            });
    });

    // Summernote (텍스트 에디터)를 초기화하는 함수
    function initializeSummernote() {
        $('.summernote').summernote({
            height: 300,
            callbacks: {
                onImageUpload: function (files) {
                    uploadSummernoteImage(files[0], $(this));
                }
            }
        });
    }

    function initializeTimeline(viewType) { // viewType을 인자로 받도록 변경
        if (isAdmin) {
            // const timelineContainer = $('#timeline-container');  이 줄은 더 이상 필요 없습니다. const
            // viewType = timelineContainer.data('view-type');  이 줄도 필요 없습니다.

            $(".timeline-item").draggable({
                handle: ".interval-bar, .dot",
                stop: function (event, ui) {
                    const item = $(this);
                    const id = item.data('id');
                    let newY = Math.round(ui.position.top / 30) * 30;
                    if (newY < 0) 
                        newY = 0;
                    
                    const dropX = event.pageX / currentScale;
                    const timelineContainer = $('#timeline-container');
                    const containerX = (
                        window.innerWidth - timelineContainer.width() * currentScale
                    ) / 2 / currentScale;

                    // ▼▼▼▼▼ 이 부분을 수정하세요 ▼▼▼▼▼ 타임라인 컨테이너의 중앙(너비의 절반)을 기준으로 좌/우를 결정합니다.
                    const newSide = (dropX - containerX < timelineContainer.width() / 2)
                        ? 'left'
                        : 'right';

                    item
                        .removeClass('left right')
                        .addClass(newSide);
                    item.css('top', newY + 'px');

                    $.ajax({
                        url: 'ajax_reorder_timeline.php',
                        type: 'POST',
                        data: {
                            id: id,
                            position_y: newY,
                            side: newSide,
                            view_type: viewType // 인자로 받은 viewType을 사용
                        },
                        dataType: 'json',
                        success: (response) => {
                            if (!response.success) {
                                alert('위치 저장 실패: ' + response.message);
                            }
                            // 위치 변경 후 타임라인을 다시 로드하여 겹침 문제를 해결합니다.
                            router();
                        },
                        error: () => {
                            alert('서버 통신 오류');
                            router();
                        }
                    });
                }
            });
        }
    }
    // AJAX로 로드된 콘텐츠 내부의 이벤트 처리를 위한 위임(delegation) 방식 글쓰기/수정 폼 제출 처리
    $(document).on('submit', 'form.ajax-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        // Summernote 에디터의 내용을 FormData에 추가
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
            success: function (response) {
                if (response.success) {
                    alert('성공적으로 처리되었습니다.');
                    // 저장 후, 해당 카테고리의 타임라인으로 이동
                    const type = form
                        .find('select[name="type"]')
                        .val() || 'overall';
                    if (type === 'overall') {
                        window.location.hash = '#/timeline';
                    } else {
                        window.location.hash = `#/${type}_timeline`;
                    }
                } else {
                    alert('오류: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('요청 처리 중 서버 오류가 발생했습니다.')
        });
    });

    // 삭제 버튼 클릭 처리
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!confirm('정말로 삭제하시겠습니까?')) 
            return;
        
        const id = $(this).data('id');
        $.ajax({
            url: 'ajax_delete_timeline.php',
            type: 'POST',
            data: {
                id: id
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('삭제되었습니다.');
                    // 삭제 후 현재 보고 있는 타임라인 목록으로 돌아갑니다.
                    window.location.hash = '#/timeline';
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            }
        });
    });

    // Summernote 에디터에 이미지 업로드 시 처리하는 함수
    function uploadSummernoteImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                if (response.success && response.url) {
                    $(editor).summernote('insertImage', response.url);
                } else {
                    alert('이미지 업로드 실패: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    }

    // --- 초기 실행 --- 창 크기가 변경될 때마다 스케일 조정
    $(window)
        .on('resize', adjustScale)
        .trigger('resize');
    // URL의 해시(#)가 변경될 때마다 라우터 실행
    $(window).on('hashchange', router);
    // 페이지 첫 로드 시 라우터 실행
    router();
});