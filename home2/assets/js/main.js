$(document).ready(function() {
    const contentContainer = $('#content');
    const isAdmin = $('nav a[href="logout.php"]').length > 0;

    function loadPage(url) {
        $.ajax({
            url: url, type: 'GET',
            success: function(response) {
                contentContainer.html(response);
                if (url.includes('timeline.php')) initializeTimeline();
                else if (url.includes('timeline_form.php')) initializeSummernote();
            },
            error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
        });
    }

    function router() {
        const hash = window.location.hash.substring(2) || 'timeline';
        const [page, params] = hash.split('?');
        
        // 새 페이지들을 라우터에 등록
        const allowed_pages = [
            'timeline', 'timeline_view', 'timeline_form',
            'novel_timeline', 'roleplay_timeline', 'trpg_timeline'
        ];

        if (allowed_pages.includes(page)) {
            const url = `${page}.php${params ? '?' + params : ''}`;
            loadPage(url);
        } else {
            loadPage('timeline.php'); // 허용되지 않은 페이지는 전체 타임라인으로
        }
    }

    function initializeSummernote() {
        $('.summernote').summernote({
            height: 300,
            callbacks: { onImageUpload: (files) => uploadSummernoteImage(files[0], $('.summernote')) }
        });
    }

    function initializeTimeline() {
        if (isAdmin) {
            const timelineContainer = $('#timeline-container');
            const timelineCenter = $('#timeline-line').offset().left;
            const viewType = timelineContainer.data('view-type'); // 현재 뷰 타입을 가져옴

            $(".timeline-item").draggable({
                handle: ".timeline-item-content.draggable",
                containment: timelineContainer,
                stop: function(event, ui) {
                    const item = $(this);
                    const id = item.data('id');
                    let newY = Math.round(ui.position.top / 30) * 30;
                    if (newY < 0) newY = 0;
                    
                    const dropX = event.pageX;
                    const newSide = (dropX < timelineCenter) ? 'left' : 'right';

                    item.removeClass('left right').addClass(newSide);
                    item.css('top', newY + 'px');

                    // 서버에 viewType을 함께 전송
                    $.ajax({
                        url: 'ajax_reorder_timeline.php',
                        type: 'POST',
                        data: { id: id, position_y: newY, side: newSide, view_type: viewType },
                        dataType: 'json',
                        success: (response) => {
                            if (!response.success) alert('위치 저장에 실패했습니다.');
                            router(); // 재정렬을 위해 새로고침
                        },
                        error: () => alert('서버 통신 오류')
                    });
                }
            });

            $('#timeline-line').on('click', function(e) {
                if (!$(e.target).closest('.timeline-item').length) {
                    const yPos = Math.round((e.pageY - $(this).offset().top) / 30) * 30;
                    window.location.hash = `#/timeline_form?y=${yPos}`;
                }
            });
        }
    }

    // Ajax Form Submission
    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);
        formData.set('content', form.find('.summernote').summernote('code'));
        $.ajax({
            url: form.attr('action'), type: 'POST', data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('성공적으로 처리되었습니다.');
                    window.location.hash = '#/timeline';
                } else {
                    alert('오류: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('요청 처리 중 서버 오류가 발생했습니다.')
        });
    });
    
    // Delete Button
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        if (!confirm('정말로 삭제하시겠습니까?')) return;
        
        const id = $(this).data('id');
        $.ajax({
            url: 'ajax_delete_timeline.php', type: 'POST', data: { id: id }, dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('삭제되었습니다.');
                    router();
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            }
        });
    });

    $(window).on('hashchange', router);
    router();
});

function uploadSummernoteImage(file, editor) {
    let data = new FormData();
    data.append("file", file);
    $.ajax({
        url: 'ajax_upload_image.php', type: "POST", data: data,
        contentType: false, processData: false, dataType: 'json',
        success: function(response) {
            if (response.success && response.url) {
                $(editor).summernote('insertImage', response.url);
            } else {
                alert('이미지 업로드 실패: ' + (response.message || '알 수 없는 오류'));
            }
        },
        error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
    });
}