$(document).ready(function () {
    const isAdmin = $('nav a[href="logout.php"]').length > 0;
    let currentScale = 1;
    let isDragging = false; 

    
    function adjustScale() {
        const container = $('.container');
        if (!container.length) 
            return;
        
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;
        let containerWidth, containerHeight;

        if (windowWidth > 786) {
            containerWidth = 1440;
            containerHeight = 900;
        } else {
            containerWidth = 720;
            containerHeight = 1280;
        }

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

    
    function initializeTimelineInteraction(viewType) {
        if (!isAdmin) 
            return;
        
        $(".draggable").draggable({
            grid: [
                0, 30
            ], 
            handle: ".item-handle",
            cursor: "move",

            start: function () {
                isDragging = true;
                $(this).css('z-index', 9999);
            },

            stop: function (event, ui) {
                const item = $(this);
                const id = item.data('id');

                
                let newY = ui.position.top;
                if (newY < 0) 
                    newY = 0;
                
                
                const timelineWrapper = $('#timeline-wrapper');
                const wrapperOffsetLeft = timelineWrapper.offset().left;
                
                // 모바일 터치와 데스크탑 클릭 모두의 좌표를 정확히 얻기 위한 코드
                let finalPageX = event.pageX;
                if (event.originalEvent.changedTouches && event.originalEvent.changedTouches.length > 0) {
                    finalPageX = event.originalEvent.changedTouches[0].pageX;
                }

                const mouseX = (finalPageX - wrapperOffsetLeft) / currentScale;
                const timelineCenter = timelineWrapper.width() / 2;
                const newSide = mouseX < timelineCenter
                    ? 'left'
                    : 'right';

                
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

                
                item.css('z-index', '');

                
                setTimeout(() => {
                    isDragging = false;
                }, 100);
            }
        });

        
        $('#timeline-line').on('click', function (e) {
            if (isDragging || $(e.target).closest('.timeline-item, a, button').length > 0) {
                return;
            }
            const timelineOffset = $(this).parent().offset().top;
            const scrollTop = $(this).parent().scrollTop();
            const clickY = (e.pageY - timelineOffset) / currentScale + scrollTop;
            const snappedY = Math.round(clickY / 30) * 30;
            window.location.hash = `#/timeline_form?y=${snappedY}`;
        });
    }

    
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
                    window.location.href = 'index.php'; // 로그인 성공 시 페이지 새로고침
                } else {
                    $('#login-error').text(response.error);
                }
            },
            error: function () {
                $('#login-error').text('로그인 중 오류가 발생했습니다.');
            }
        });
    });

    
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

    
    $(window)
        .on('resize', adjustScale)
        .trigger('resize');
    $(window).on('hashchange', router);
    router();
});