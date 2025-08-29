$(document).ready(function () {
    // 관리자 여부를 <nav>에 logout.php 링크가 있는지로 확인
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
        currentScale = scale; // 현재 스케일 저장 (클릭 위치 계산에 사용)

        container.css({
            'transform': `scale(${scale})`,
            'left': `${ (windowWidth - containerWidth * scale) / 2}px`,
            'top': `${ (windowHeight - containerHeight * scale) / 2}px`
        });
        container.show();
    }

    // SPA의 핵심, URL 해시(hash)에 따라 적절한 콘텐츠를 보여주는 라우터 함수
    function router() {
        const hash = window.location.hash || '#/timeline';

        $('.view').removeClass('active');
        $('#ajax-content-view').empty();

        if (hash.startsWith('#/timeline_form') || hash.startsWith('#/timeline_view')) {
            // 상세 보기 또는 폼 요청일 경우
            $('#ajax-content-view').addClass('active');
            let url = hash.substring(2); // 예: "timeline_view?id=4"
            const queryIndex = url.indexOf('?');
            if (queryIndex !== -1) {
                const base = url.substring(0, queryIndex);
                const query = url.substring(queryIndex);
                url = base + '.php' + query; // 결과: "timeline_view.php?id=4"
            } else {
                url = url + '.php';
            }
            loadAjaxPage(url);
        } else if (hash.startsWith('#/login')) {
            // 로그인 페이지 요청
            $('#login-view').addClass('active');
        } else {
            // 그 외 모든 경우 (타임라인 목록)
            $('#timeline-container').addClass('active');
            loadTimeline(hash);
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
            if (url.includes('timeline_form.php')) {
                initializeSummernote();
            }
        });
    }

    // 타임라인 목록을 로드하고 초기화하는 함수
    function loadTimeline(hash) {
        let timelineType = 'overall';
        if (hash.includes('_timeline')) {
            timelineType = hash
                .split('_')[0]
                .substring(2);
        }
        $('#timeline-container').load(
            'timeline.php?timeline_type=' + timelineType,
            function () {
                initializeTimelineInteraction(timelineType);
            }
        );
    }

    // 타임라인 상호작용(드래그, 클릭 추가)을 초기화하는 함수
    function initializeTimelineInteraction(viewType) {
        if (!isAdmin) 
            return; // 관리자가 아니면 실행하지 않음
        
        const timelineWrapper = $("#timeline-wrapper");

        // 드래그 앤 드롭 기능 설정
        $(".draggable").draggable({
            grid: [
                0, 30
            ], // 세로 30px 간격으로 스냅
            handle: ".dot, .interval-bar",
            stop: function (event, ui) {
                const item = $(this);
                const id = item.data('id');
                let newY = ui.position.top;
                if (newY < 0) 
                    newY = 0;
                
                // 드롭된 위치의 x좌표를 기반으로 side 결정
                const dropX = event.pageX;
                const windowCenterX = window.innerWidth / 2;
                const newSide = dropX < windowCenterX
                    ? 'left'
                    : 'right';

                item
                    .removeClass('left right')
                    .addClass(newSide);

                $.ajax({
                    url: 'ajax_reorder_timeline.php',
                    type: 'POST',
                    data: {
                        id: id,
                        position_y: newY,
                        side: newSide,
                        view_type: viewType
                    },
                    dataType: 'json',
                    success: (response) => {
                        if (!response.success) {
                            alert('위치 저장 실패: ' + response.message);
                        }
                        // 위치 변경 후 타임라인을 다시 로드하여 겹침 문제를 해결
                        router();
                    },
                    error: () => {
                        alert('서버 통신 오류');
                        router();
                    }
                });
            }
        });

        // 타임라인 빈 공간 클릭 시 새 글 작성 폼으로 이동
        timelineWrapper.on('click', function (e) {
            // 아이템이나 링크 등 다른 요소가 클릭된 경우는 제외
            if ($(e.target).closest('.timeline-item, a, button').length === 0) {
                const timelineOffset = $(this)
                    .offset()
                    .top;
                const scrollTop = $(this).scrollTop();
                const clickY = e.pageY - timelineOffset + scrollTop;

                // 30px 그리드에 맞춤
                const snappedY = Math.round(clickY / 30) * 30;

                window.location.hash = `#/timeline_form?y=${snappedY}`;
            }
        });
    }

    // Summernote (텍스트 에디터) 초기화 함수
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

    // Summernote 에디터에 이미지 업로드 시 처리 함수
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
                    editor.summernote('insertImage', response.url);
                } else {
                    alert('이미지 업로드 실패: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    }

    // 로그인 폼 제출 AJAX 처리
    $('#login-form').on('submit', function (event) {
        event.preventDefault();
        const formData = $(this).serialize();
        $('#login-error').text('');

        $.ajax({
            url: 'login_process.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = 'index.php'; // 로그인 성공 시 새로고침
                } else {
                    $('#login-error').text(response.error);
                }
            },
            error: function () {
                $('#login-error').text('로그인 중 오류가 발생했습니다.');
            }
        });
    });

    // AJAX로 로드된 콘텐츠 내의 이벤트 처리를 위한 위임(delegation)

    // 1. 글쓰기/수정 폼 제출
    $(document).on('submit', 'form.ajax-form', function (e) {
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
            success: function (response) {
                if (response.success) {
                    alert('성공적으로 저장되었습니다.');
                    window.location.hash = '#/timeline'; // 저장 후 전체 타임라인으로 이동
                } else {
                    alert('오류: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('요청 처리 중 서버 오류가 발생했습니다.')
        });
    });

    // 2. 삭제 버튼 클릭
    $(document).on('click', '.delete-btn', function () {
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
                    window.location.hash = '#/timeline'; // 삭제 후 전체 타임라인으로 이동
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
            error: function () {
                alert('삭제 요청 중 서버 오류가 발생했습니다.');
            }
        });
    });

    // --- 초기 실행 ---
    $(window)
        .on('resize', adjustScale)
        .trigger('resize');
    $(window).on('hashchange', router);
    router(); // 페이지 첫 로드 시 라우터 실행
});