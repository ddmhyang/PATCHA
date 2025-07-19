/**
 * ===================================================================
 * DolfoLil 프로젝트 메인 JavaScript (최종 완성본)
 * - 중복된 폼 이벤트 핸들러를 통합하여 오류 해결
 * - 메신저 전송 시 창이 꺼지지 않고, 채팅만 추가되도록 수정
 * ===================================================================
 */
$(document).ready(function () {

    const contentContainer = $('#content-container');
    const messengerOverlay = $('#messenger-overlay');

    // --- 핵심 함수 (페이지 로딩, 라우팅 등) ---
    function loadPage(url) {
        $.ajax({
            url: `api.php?${url}`,
            type: 'GET',
            success: function (response) {
                contentContainer.html(response);
                const pageName = new URLSearchParams(url).get('page');
                updateSMLine(pageName);
            },
            error: function () {
                contentContainer.html('<h2>페이지를 불러오는 데 실패했습니다.</h2>');
            }
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

    // --- 이벤트 핸들러 ---
    $(document).on('click', 'a', function (e) {
        const href = $(this).attr('href');
        if (href && href.startsWith('#/')) {
            e.preventDefault();
            window.location.hash = href;
        }
    });

    $(document).on('submit', 'form', function (e) {
        e.preventDefault();
        var form = $(this);
        var formData = new FormData(this);

        if (form.find('.summernote').length) {
            formData.set('content', form.find('.summernote').summernote('code'));
        }

        if (form.attr('id') === 'messenger-form') {
            var messageInput = form.find('input[name="message"]');
            var characterSelect = form.find('select[name="character"]');
            var messageText = messageInput.val();
            var characterName = characterSelect.val();

            if (!messageText.trim()) return;

            $.ajax({
                url: form.attr('action'), type: 'POST', data: formData,
                processData: false, contentType: false, dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const safeMessage = messageText.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        let profileHtml = (characterName === 'Adolfo') ? '<div class="phone_profile1"></div>' : '<div class="phone_profile2"></div>';
                        let chatHtml = (characterName === 'Adolfo') ?
                            `<div class="phone_chat1"><a>${safeMessage}</a></div>` :
                            `<div class="phone_chat2"><a>${safeMessage}</a></div>`;
                        const newMessageBox = $('<div class="message-row ' + (characterName === 'Lilian' ? 'right' : '') + '">' + profileHtml + chatHtml + '</div>');

                        const messageList = $('#message-list');
                        messageList.append(newMessageBox);
                        messageList.scrollTop(messageList[0].scrollHeight);
                        messageInput.val('');
                    } else {
                        alert('전송 실패: ' + (response.message || '알 수 없는 오류'));
                    }
                },
                error: () => alert('메신저 전송 중 오류가 발생했습니다.')
            });
        } else {
            var pageContainer = form.closest('.page-container');
            $.ajax({
                url: form.attr('action'), type: 'POST', data: formData,
                processData: false, contentType: false, dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('성공적으로 처리되었습니다.');
                        if (form.hasClass('edit-form')) {
                            pageContainer.find('.content-display').html(formData.get('content'));
                            pageContainer.find('#edit-mode').hide();
                            pageContainer.find('#view-mode').show();
                        } else if (response.redirect_url) {
                            window.location.hash = response.redirect_url;
                        }
                    } else {
                        alert('오류: ' + (response.message || '알 수 없는 오류'));
                    }
                },
                error: () => alert('요청 처리 중 오류가 발생했습니다.')
            });
        }
    });

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

    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        if (!confirm('정말 삭제하시겠습니까?')) return;
        var postId = $(this).data('id');
        var type = $(this).data('type');
        $.ajax({
            url: `../actions/${type}_delete.php`,
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

    $(window).on('hashchange', router).trigger('hashchange');
    
    window.addEventListener('load', () => {
        adjustScale();
        document.body.style.visibility = 'visible';
    });
    window.addEventListener('resize', adjustScale);
});