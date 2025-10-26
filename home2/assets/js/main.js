$(document).ready(function () {
    const isAdmin = $('nav a[href="logout.php"]').length > 0;
    let currentScale = 1;
    let isDragging = false;
    const bgmPlayer = document.getElementById('bgm-player');

    function adjustScale() {
        const container = $('.container');
        if (!container.length) 
            return;
        
        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;
        let containerWidth,
            containerHeight;

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
                setTimeout(adjustTimelineHeight, 100);
            }
        );
    }

    function adjustTimelineHeight() {
        const timelineWrapper = $('#timeline-wrapper');
        if (timelineWrapper.length === 0) 
            return;
        
        let maxPosition = 0;
        $('.timeline-item').each(function () {
            const itemBottom = $(this)
                .position()
                .top + $(this).outerHeight();
            if (itemBottom > maxPosition) {
                maxPosition = itemBottom;
            }
        });

        const minHeight = window.innerWidth > 786
            ? 900
            : 1280;
        let newHeight = maxPosition + 500;

        if (newHeight < minHeight) {
            newHeight = minHeight;
        }
        timelineWrapper.height(newHeight);
    }

    function initializeTimelineInteraction(viewType) {
        if (!isAdmin) 
            return;
        
        $(".draggable").each(function () {
            const item = $(this);
            let startY,
                startX,
                offsetY,
                offsetX;
            let dragging = false;

            function dragStart(pageX, pageY) {
                dragging = true;
                item.css("z-index", 9999);

                const pos = item.position();
                startX = pageX;
                startY = pageY;
                offsetX = pos.left;
                offsetY = pos.top;
            }
            
            function dragMove(pageX, pageY) {
                if (!dragging) 
                    return;
                
                const dx = (pageX - startX) / currentScale;
                const containerOffsetTop = $('.container')
                    .offset()
                    .top;
                const mainScrollTop = $('main').scrollTop();

                const mouseYInContainer = ((pageY - containerOffsetTop) / currentScale) + mainScrollTop;

                const newTop = mouseYInContainer - 20;

                item.css({
                    left: offsetX + dx,
                    top: newTop
                });

                const timelineWrapper = $('#timeline-wrapper');
                const currentWrapperHeight = timelineWrapper.height();
                const itemBottomPosition = newTop + item.outerHeight();
                const buffer = 200;

                if (itemBottomPosition + buffer > currentWrapperHeight) {
                    timelineWrapper.height(itemBottomPosition + buffer);
                }
            }

            function dragEnd(pageX, pageY) {
                if (!dragging) 
                    return;
                dragging = false;
                item.css("z-index", "");

                let newY = item
                    .position()
                    .top;
                if (newY < 0) 
                    newY = 0;
                
                const timelineWrapper = $("#timeline-wrapper");
                const wrapperOffsetLeft = timelineWrapper
                    .offset()
                    .left;
                const mouseX = (pageX - wrapperOffsetLeft) / currentScale;
                const timelineCenter = timelineWrapper.width() / 2;
                const newSide = mouseX < timelineCenter
                    ? "left"
                    : "right";

                $.ajax({
                    url: "ajax_reorder_timeline.php",
                    type: "POST",
                    data: {
                        id: item.data("id"),
                        position_y: newY,
                        side: newSide,
                        view_type: viewType
                    },
                    dataType: "json",
                    success: (response) => {
                        if (!response.success) {
                            alert("위치 저장 실패: " + response.message);
                        }
                        adjustTimelineHeight();
                        router();
                    },
                    error: () => {
                        alert("서버 통신 오류");
                        router();
                    }
                });
            }

            item.on("mousedown", function (e) {
                e.preventDefault();
                dragStart(e.pageX, e.pageY);
                $(document).on("mousemove.drag", function (e) {
                    dragMove(e.pageX, e.pageY);
                });
                $(document).on("mouseup.drag", function (e) {
                    $(document).off(".drag");
                    dragEnd(e.pageX, e.pageY);
                });
            });
            item.on("touchstart", function (e) {
                e.preventDefault();
                const touch = e.originalEvent.touches[0];
                dragStart(touch.pageX, touch.pageY);
                $(document).on("touchmove.drag", function (e) {
                    const touch = e.originalEvent.touches[0];
                    dragMove(touch.pageX, touch.pageY);
                });
                $(document).on("touchend.drag touchcancel.drag", function (e) {
                    $(document).off(".drag");
                    const touch = e.originalEvent.changedTouches[0];
                    dragEnd(touch.pageX, touch.pageY);
                });
            });
        });

        $("#timeline-line").on("click", function (e) {
            if ($(e.target).closest(".timeline-item, a, button").length > 0) 
                return;
            
            const timelineOffset = $(this).parent().offset().top;
            const mainScrollTop = $('main').scrollTop();

            const clickY = ((e.pageY - timelineOffset) / currentScale) + mainScrollTop;
            
            const snappedY = Math.round(clickY / 30) * 30;
            window.location.hash = `#/timeline_form?y=${snappedY}`;
        });
    }

    
    function initializeSummernote() {
        $('.summernote').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'attachFile']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            buttons: {
                attachFile: function (context) {
                    var ui = $.summernote.ui;
                    var button = ui.button({
                        contents: '<i class="note-icon-paperclip"/> 파일',
                        tooltip: '파일 첨부',
                        click: function () {
                            const fileInput = document.createElement('input');
                            fileInput.setAttribute('type', 'file');
                            fileInput.setAttribute('accept', '*/*');
                            fileInput.addEventListener('change', function (e) {
                                const file = e.target.files[0];
                                if (file) {
                                    uploadSummernoteFile(file, $('.summernote'));
                                }
                                $(fileInput).remove();
                            });
                            document.body.appendChild(fileInput);
                            fileInput.click();
                        }
                    });
                    return button.render();
                }
            },
            callbacks: {
                onImageUpload: function (files) {
                    uploadSummernoteImage(files[0], $(this));
                }
            }
        });
    }

    function uploadSummernoteFile(file, editor) {
        let data = new FormData();
        data.append("file", file);

        $.ajax({
            url: 'ajax_upload_file.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                if (response.success && response.url) {
                    alert('파일 업로드 성공!');
                    const link = document.createElement('a');
                    link.href = response.url;
                    link.textContent = response.fileName;
                    link.setAttribute('target', '_blank');
                    link.setAttribute('download', response.fileName);

                    editor.summernote('insertNode', link);
                } else {
                    alert('파일 업로드 실패: ' + (
                        response.message || '알 수 없는 오류'
                    ));
                }
            },
            error: () => alert('파일 업로드 중 서버 오류가 발생했습니다.')
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
                    window.location.href = 'index.php';
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

document.addEventListener('DOMContentLoaded', () => {
    const musicPlayer = document.getElementById('music-player');

    const playMusicOnClick = () => {
        if (musicPlayer && musicPlayer.paused) {
            musicPlayer
                .play()
                .catch(error => {
                    console.error("Music autoplay failed:", error);
                });
        }
        document
            .body
            .removeEventListener('click', playMusicOnClick);
    };

    document
        .body
        .addEventListener('click', playMusicOnClick);
});