/**
 * ===================================================================
 * DolfoLil 프로젝트 메인 JavaScript (최종 수정본)
 * - AJAX 경로 수정, Summernote 수정/보기 모드 전환 로직 추가
 * ===================================================================
 */
$(document).ready(function () {

    const contentContainer = $('#content-container');
    const messengerOverlay = $('#messenger-overlay');

    // --- 핵심 함수 ---
    function loadPage(url) {
        $.ajax({
            url: `api.php?${url}`, type: 'GET',
            success: function (response) {
                contentContainer.html(response);
                const pageName = new URLSearchParams(url).get('page');
                updateSMLine(pageName);
            },
            error: () => contentContainer.html('<h2>페이지 로딩 실패</h2>')
        });
    }

    function updateSMLine(pageName) {
        let styles = {};
        let pageGroup = pageName;
        if (pageName && (pageName.startsWith('gallery_') || pageName.startsWith('trpg_'))) {
            pageGroup = pageName.split('_')[0];
        } else if (pageName === 'adolfo' || pageName === 'lilian') {
            pageGroup = 'dolfolil';
        }

        switch (pageGroup) {
            case 'main_content': styles = { top: '85px', width: '80px' }; break;
            case 'dolfolil': styles = { top: '182px', width: '90px' }; break;
            case 'gallery': styles = { top: '284px', width: '95px' }; break;
            case 'trpg': styles = { top: '384px', width: '80px' }; break;
            case 'messenger': styles = { top: '468px', width: '120px' }; break;
            default: $('.sMLine').hide(); return;
        }
        $('.sMLine').show().css(styles);
    }

    function router() {
        const path = window.location.hash.substring(2) || 'main_content';
        const [page, queryString] = path.split('?');
        
        if (page === 'messenger') {
            toggleMessenger();
            updateSMLine('messenger');
        } else {
            messengerOverlay.fadeOut();
            loadPage(`page=${page}${queryString ? '&' + queryString : ''}`);
        }
    }

    function toggleMessenger() {
        if (messengerOverlay.is(':visible')) {
            messengerOverlay.fadeOut();
        } else {
            $.get(`api.php?page=messenger`, (response) => {
                messengerOverlay.html(response).fadeIn();
            });
        }
    }

    // Summernote 이미지 업로드 함수 (경로 수정됨)
    function uploadImage(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: '../actions/ajax_upload_image.php', // ✅ 경로 수정
            type: "POST", data: data,
            contentType: false, processData: false, dataType: 'json',
            success: function(response) {
                if (response.success && response.urls) {
                    response.urls.forEach(url => editor.summernote('insertImage', url));
                } else {
                    alert('이미지 업로드 실패: ' + response.error);
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류 발생')
        });
    }

    // --- 이벤트 핸들러 ---
    $(document).on('click', 'a', function (e) {
        const href = $(this).attr('href');
        if (href && href.startsWith('#/')) {
            e.preventDefault();
            window.location.hash = href;
        }
    });

    // 모든 폼 제출 처리 (경로 수정됨)
    $(document).on('submit', 'form', function (e) {
        e.preventDefault();
        var form = $(this);
        var pageContainer = form.closest('.page-container');
        if (form.find('.summernote').length) {
            form.find('textarea[name="content"]').val(form.find('.summernote').summernote('code'));
        }
        
        $.ajax({
            url: form.attr('action'), // 폼의 action 속성 값을 그대로 사용
            type: 'POST', data: new FormData(this),
            processData: false, contentType: false, dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (form.hasClass('edit-form')) {
                        alert('성공적으로 저장되었습니다.');
                        pageContainer.find('.content-display').html(form.find('textarea[name="content"]').val());
                        pageContainer.find('#edit-mode').hide();
                        pageContainer.find('#view-mode').show();
                    } else if (form.attr('id') === 'messenger-form') {
                         router();
                    } else {
                        alert('성공적으로 처리되었습니다.');
                        if (response.redirect_url) {
                            window.location.hash = response.redirect_url;
                        }
                    }
                } else {
                    alert('오류: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('요청 처리 중 오류가 발생했습니다.')
        });
    });

    $(document).on('submit', 'form#messenger-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var messageInput = form.find('input[name="message"]');
        var characterSelect = form.find('select[name="character"]');

        // 전송할 데이터 미리 저장
        var messageText = messageInput.val();
        var characterName = characterSelect.val();

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: new FormData(this),
            processData: false, contentType: false, dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // 1. 성공 시, 새 말풍선 HTML을 동적으로 생성
                    let profileHtml, chatHtml;
                    if (characterName === 'Adolfo') {
                        profileHtml = '<div class="phone_profile1"></div>';
                        chatHtml = '<div class="phone_chat1"><a>' + messageText + '</a></div>';
                    } else { // Lilian
                        profileHtml = '<div class="phone_chat2"><a>' + messageText + '</a></div>';
                        chatHtml = '<div class="phone_profile2"></div>';
                    }
                    const newMessageBox = $('<div class="message-row ' + (characterName === 'Lilian' ? 'right' : '') + '">' + profileHtml + chatHtml + '</div>');

                    // 2. 채팅 목록에 새 말풍선 추가하고 스크롤 내리기
                    const messageList = $('#message-list');
                    messageList.append(newMessageBox);
                    messageList.scrollTop(messageList[0].scrollHeight);

                    // 3. 입력창 비우기
                    messageInput.val('');
                } else {
                    alert('전송 실패: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('메신저 전송 중 오류가 발생했습니다.')
        });
    });

    // ▼▼▼ 메신저 삭제 로직 추가 (오류 해결) ▼▼▼
    let pressTimer;
    $(document).on('mousedown', '.message-item[data-is-admin="true"]', function() {
        let messageElement = $(this);
        pressTimer = window.setTimeout(function() {
            if (confirm('이 메시지를 삭제하시겠습니까?')) {
                let messageId = messageElement.data('id');
                $.post('../actions/messenger_delete.php', { id: messageId, csrf_token: csrfToken }, function(response) {
                    if (response.success) {
                        messageElement.fadeOut(300, function() { $(this).remove(); });
                    } else {
                        alert('삭제 실패: ' + response.message);
                    }
                }, 'json');
            }
        }, 800);
    }).on('mouseup mouseleave', '.message-item[data-is-admin="true"]', function() {
        clearTimeout(pressTimer);
    });

    // 삭제 버튼 처리 (경로 수정됨)
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        if (!confirm('정말 삭제하시겠습니까?')) return;
        var postId = $(this).data('id');
        var type = $(this).data('type');
        $.ajax({
            url: `../actions/${type}_delete.php`, // ✅ 경로 수정
            type: 'POST', data: { id: postId, token: csrfToken }, dataType: 'json',
            success: function (response) {
                if (response.success && response.redirect_url) {
                    alert('삭제되었습니다.');
                    window.location.hash = response.redirect_url;
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
            error: () => alert('삭제 요청 처리 중 오류가 발생했습니다.')
        });
    });
    
    // 수정/보기 모드 전환 버튼
    $(document).on('click', '#edit-btn', function() {
        var pageContainer = $(this).closest('.page-container');
        pageContainer.find('#view-mode').hide();
        pageContainer.find('#edit-mode').show();
    });
    $(document).on('click', '#cancel-btn', function() {
        var pageContainer = $(this).closest('.page-container');
        pageContainer.find('#edit-mode').hide();
        pageContainer.find('#view-mode').show();
    });

    // --- 반응형 스케일링 ---
    function adjustScale() {
        const container = document.querySelector('.container');
        if (!container) return;
        const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
        let containerWidth, containerHeight;

        if (windowWidth <= 784) {
            containerWidth = 720; containerHeight = 1280;
        } else {
            containerWidth = 1440; containerHeight = 900;
        }
        const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
        container.style.transform = `scale(${scale})`;
        container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
        container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
    }

    // --- 초기 실행 ---
    $(window).on('hashchange', router).trigger('hashchange');
    
    window.addEventListener('load', () => {
        adjustScale();
        document.body.style.visibility = 'visible';
    });
    window.addEventListener('resize', adjustScale);
});