$(document).ready(function () {
    const isAdmin = $('nav a[href="logout.php"]').length > 0;
    let currentScale = 1;
    let isDragging = false; // 드래그 상태를 추적하는 플래그

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
            'transform': `scale(${scale})`,
            'left': `${ (windowWidth - containerWidth * scale) / 2}px`,
            'top': `${ (windowHeight - containerHeight * scale) / 2}px`
        });
        container.show();
    }

    // SPA 라우터
    function router() {
        const hash = window.location.hash || '#/timeline';
        $('.view').removeClass('active');
        $('#ajax-content-view').empty();

        if (hash.startsWith('#/timeline_form') || hash.startsWith('#/timeline_view')) {
            $('#ajax-content-view').addClass('active');
            let url = hash.substring(2);
            const queryIndex = url.indexOf('?');
            if (queryIndex !== -1) {
                const base = url.substring(0, queryIndex);
                const query = url.substring(queryIndex);
                url = base + '.php' + query;
            } else {
                url = url + '.php';
            }
            loadAjaxPage(url);
        } else if (hash.startsWith('#/login')) {
            $('#login-view').addClass('active');
        } else {
            $('#timeline-container').addClass('active');
            loadTimeline(hash);
        }
    }

    // AJAX 페이지 로드
    function loadAjaxPage(url) {
        $('#ajax-content-view').load(url, function (response, status, xhr) {
            if (status == "error") {
                $(this).html("<h2>페이지를 불러올 수 없습니다.</h2>");
            }
            if (url.includes('timeline_form.php')) {
                initializeSummernote();
            }
        });
    }

    // 타임라인 로드
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

    // *** 드래그 로직 최종 완성본 ***
    function initializeTimelineInteraction(viewType) {
        if (!isAdmin) 
            return;
        
        $(".draggable").draggable({
            grid: [
                0, 30
            ], // Y축으로 30px씩 스냅
            handle: ".item-handle",
            cursor: "move",

            start: function () {
                isDragging = true;
                $(this).css('z-index', 9999);
            },

            stop: function (event, ui) {
                const item = $(this);
                const id = item.data('id');

                // 최종 Y 위치 계산
                let newY = ui.position.top;
                if (newY < 0) 
                    newY = 0;
                
                // **핵심: 마우스 X 좌표로 side(left/right) 결정**
                const timelineWrapper = $('#timeline-wrapper');
                // 화면 배율(scale)을 고려하여 실제 클릭 위치 계산
                const wrapperOffsetLeft = timelineWrapper
                    .offset()
                    .left;
                const mouseX = (event.pageX - wrapperOffsetLeft) / currentScale;
                const timelineCenter = timelineWrapper.width() / 2;
                const newSide = mouseX < timelineCenter
                    ? 'left'
                    : 'right';

                // 서버에 위치와 방향 전송
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
                        if (response.success) {
                            // 저장이 성공하면 타임라인을 새로고침하여 겹침 등을 재계산
                            router();
                        } else {
                            alert('위치 저장 실패: ' + response.message);
                            router();
                        }
                    },
                    error: () => {
                        alert('서버 통신 오류');
                        router();
                    }
                });

                // z-index 초기화
                item.css('z-index', '');

                // 드래그 종료 플래그 설정 (클릭 이벤트 방지용)
                setTimeout(() => {
                    isDragging = false;
                }, 100);
            }
        });

        // 빈 공간 클릭 시 새 글 작성
        $('#timeline-wrapper').on('click', function (e) {
            if (isDragging || $(e.target).closest('.timeline-item, a, button').length > 0) {
                return;
            }
            const timelineOffset = $(this)
                .offset()
                .top;
            const scrollTop = $(this).scrollTop();
            const clickY = (e.pageY - timelineOffset) / currentScale + scrollTop;
            const snappedY = Math.round(clickY / 30) * 30;
            window.location.hash = `#/timeline_form?y=${snappedY}`;
        });
    }

    // Summernote 에디터 초기화
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

    // Summernote 이미지 업로드
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

    // 로그인 폼 제출
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
                    window
                        .location
                        .reload();
                } else {
                    $('#login-error').text(response.error);
                }
            },
            error: function () {
                $('#login-error').text('로그인 중 오류가 발생했습니다.');
            }
        });
    });

    // Ajax 폼 제출 (글쓰기/수정)
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
                    window.location.hash = '#/timeline';
                } else {
                    alert('오류: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('요청 처리 중 서버 오류가 발생했습니다.')
        });
    });

    // 삭제 버튼
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
                    window.location.hash = '#/timeline';
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
    router();
});