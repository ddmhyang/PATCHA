$(document).ready(function () {

    const contentContainer = $('#content-container');
    const messengerOverlay = $('#messenger-overlay');

    
    const bgmPlayer = document.getElementById('bgm-player');
    const playBtn = $('.bottom_play_btn');
    const prevBtn = $('.bottom_pre_btn');
    const nextBtn = $('.bottom_next_btn');

    
    const playIcon = '<path d="M38 22L0.5 43.6506V0.349365L38 22Z" fill="#FAFAFA"/>';
    const pauseIcon = '<path d="M0 0 H12 V44 H0 Z M26 0 H38 V44 H26 Z" fill="#FAFAFA"/>';

    
    function updatePlayButtonIcon() {
        if (bgmPlayer.paused) {
            playBtn.html(playIcon);
            playBtn.css('left', '708px');
        } else {
            playBtn.html(pauseIcon);
            playBtn.css('left', '702px');
        }
    }

    let windowWidth = $(window).width(); // 현재 창 너비 가져오기
    
    if (windowWidth <= 784) {
        function updatePlayButtonIcon() {
            if (bgmPlayer.paused) {
                playBtn.html(playIcon);
                playBtn.css('left', '354px');
            } else {
                playBtn.html(pauseIcon);
                playBtn.css('left', '348px');
            }
        }
    }

    playBtn.on('click', function () {
        if (bgmPlayer.paused) {
            bgmPlayer.play();
        } else {
            bgmPlayer.pause();
        }
    });
    
    prevBtn.on('click', function () {
        bgmPlayer.currentTime -= 10;
    });
    
    nextBtn.on('click', function () {
        bgmPlayer.currentTime += 10;
    });
    
    $(bgmPlayer).on('play pause', updatePlayButtonIcon);

    
    $(document).one('click', function () {
        if (bgmPlayer.paused) {
            bgmPlayer.play();
        }
    });
    
    function loadPage(url) {
        $.ajax({
            url: `main.php?spa_content=true&${url}`,
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
            case 'main_content':
                styles = {
                    top: '85px',
                    width: '80px'
                };
                break;
            case 'dolfolil':
                styles = {
                    top: '182px',
                    width: '90px'
                };
                break;
            case 'gallery':
                styles = {
                    top: '284px',
                    width: '95px'
                };
                break;
            case 'trpg':
                styles = {
                    top: '384px',
                    width: '80px'
                };
                break;
            case 'messenger':
                styles = {
                    top: '485px',
                    width: '120px'
                };
                break;
            default:
                $('.sMLine').hide();
                return;
        }
        let windowWidth = $(window).width(); // 현재 창 너비 가져오기

        if (windowWidth <= 784) {
            // 가로 684px 이하일 때의 left 값 설정
            // 이 값들은 예시이며, 실제 원하는 위치에 맞게 조절해야 합니다.
                switch (pageGroup) {
                    case 'main_content':
                        styles = {
                            top: '55px',
                            left: '40px',
                            width: '60px'
                        };
                        break;
                    case 'dolfolil':
                        styles = {
                            top: '55px',
                            left: '138px',
                            width: '88px'
                        };
                        break;
                    case 'gallery':
                        styles = {
                            top: '55px',
                            left: '244px',
                            width: '80px'
                        };
                        break;
                    case 'trpg':
                        styles = {
                            top: '55px',
                            left: '341px',
                            width: '60px'
                        };
                        break;
                    case 'messenger':
                        styles = {
                            top: '55px',
                            left: '448px',
                            width: '115px'
                        };
                        break;
                    default:
                        $('.sMLine').hide();
                        return;
                }
        }
        $('.sMLine')
            .show()
            .css(styles);
    }

    function router() {
        const path = window.location.hash.substring(2) || 'main_content';
        const [page, queryString] = path.split('?');
        const finalUrl = `page=${page}${queryString ? '&' + queryString : ''}`;

        if (page === 'messenger') {
            toggleMessenger(finalUrl);
        } else {
            messengerOverlay.fadeOut();
            loadPage(finalUrl);
        }
    }

   function toggleMessenger(url) {
        if (messengerOverlay.is(':visible')) {
            messengerOverlay.fadeOut();
        } else {
            $.get(`main.php?spa_content=true&${url}`, (response) => {
                messengerOverlay.html(response).fadeIn();
            });
        }
    }

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
            
            if (typeof isLoggedIn === 'undefined' || !isLoggedIn) {
                alert('권한이 없습니다.');
                return;
            }

            var messageInput = form.find('input[name="message"]');
            var characterSelect = form.find('select[name="character"]');
            var messageText = messageInput.val();
            var characterName = characterSelect.val();

            if (!messageText.trim()) 
                return;
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const safeMessage = messageText
                            .replace(/</g, "&lt;")
                            .replace(/>/g, "&gt;");
                        const profilePic = (characterName === 'Adolfo')
                            ? 'torken.png'
                            : 'torken2.png';
                        const messageSide = (characterName === 'Adolfo')
                            ? 'received'
                            : 'sent';

                        const newMessageHtml = `
                            <div class="message-row ${messageSide}" data-id="${response.new_id}" data-is-admin="true">
                                <img class="profile-pic" src="../assets/img/${profilePic}">
                                <div class="message-bubble">
                                    <div class="character-name">${characterName}</div>
                                    <p class="message-text">${safeMessage}</p>
                                </div>
                            </div>`;

                        const messageList = $('#message-list');
                        messageList.append(newMessageHtml);
                        messageList.scrollTop(messageList[0].scrollHeight);
                        messageInput.val('');
                    } else {
                        alert('전송 실패: ' + (
                            response.message || '알 수 없는 오류'
                        ));
                    }
                },
                error: () => alert('메신저 전송 중 오류가 발생했습니다.')
            });
        } else {
            var pageContainer = form.closest('.page-container');
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert(response.message || '성공적으로 처리되었습니다.');
                        if (form.hasClass('edit-form')) {
                            pageContainer
                                .find('.content-display')
                                .html(formData.get('content'));
                            pageContainer
                                .find('#edit-mode')
                                .hide();
                            pageContainer
                                .find('#view-mode')
                                .show();
                        } else if (response.redirect_url) {
                            window.location.hash = response.redirect_url;
                        }
                    } else {
                        alert('오류: ' + (
                            response.message || '알 수 없는 오류'
                        ));
                    }
                },
                error: () => alert('요청 처리 중 오류가 발생했습니다.')
            });
        }
    });

    let pressTimer;
    $(document)
        .on('mousedown', '.message-row[data-is-admin="true"]', function () {
            let messageElement = $(this);
            pressTimer = window.setTimeout(function () {
                let messageIdRaw = messageElement.data('id');

                console.log('메시지 삭제 시도 - 원본 messageId:', messageIdRaw, typeof messageIdRaw);

                if (typeof messageIdRaw === 'undefined' || messageIdRaw === null || messageIdRaw === '') {
                    alert('삭제 실패: 유효한 메시지 ID를 찾을 수 없습니다. (클라이언트 측 - 값 없음)');
                    console.error('클라이언트 측 오류: messageElement.dataset.id가 유효하지 않음 (값 없음)', messageIdRaw);
                    return;
                }

                let messageId = Number(messageIdRaw);

                if (isNaN(messageId) || messageId <= 0) {
                     alert('삭제 실패: 유효한 메시지 ID가 아닙니다. (클라이언트 측 - 변환 오류)');
                     console.error('클라이언트 측 오류: 변환된 messageId가 유효하지 않음 (NaN 또는 0 이하)', messageId);
                     return;
                }
                
                if (confirm('이 메시지를 삭제하시겠습니까?')) {
                    $.post('messenger_delete.php', {
                        id: messageId,
                        csrf_token: csrfToken
                    }, function (response) {
                        if (response.success) {
                            messageElement.fadeOut(300, function () {
                                $(this).remove();
                            });
                        } else {
                            alert('삭제 실패: ' + response.message);
                        }
                    }, 'json');
                }
            }, 500);
        })
        .on('mouseup mouseleave', '.message-row[data-is-admin="true"]', function () {
            clearTimeout(pressTimer);
        });

    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        if (!confirm('정말 삭제하시겠습니까?')) 
            return;
        var postId = $(this).data('id');
        var type = $(this).data('type');
        $.ajax({
            url: `pages/${type}_delete.php`,
            type: 'POST',
            data: {
                id: postId,
                token: csrfToken
            },
            dataType: 'json',
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

    $(document).on('click', '#edit-btn', function () {
        var pageContainer = $(this).closest('.page-container');
        pageContainer
            .find('#view-mode')
            .hide();
        pageContainer
            .find('#edit-mode')
            .show();
    });
    $(document).on('click', '#cancel-btn', function () {
        var pageContainer = $(this).closest('.page-container');
        pageContainer
            .find('#edit-mode')
            .hide();
        pageContainer
            .find('#view-mode')
            .show();
    });

    function closeMessengerAndResetHash() {
        if (messengerOverlay.is(':visible')) {
            messengerOverlay.fadeOut();
            if (window.location.hash === '#/messenger') {
                window.location.hash = '#/main_content';
            }
        }
    }

    $(document).on('click', '.chat-header', function (e) {
        e.stopPropagation();
        closeMessengerAndResetHash();
    });

    $(document).on('click', function (e) {
        if (messengerOverlay.is(':visible') && !$(e.target).closest('.chat-container').length && !$(e.target).closest('a[data-page="messenger"]').length) {
            closeMessengerAndResetHash();
        }
    });

    function adjustScale() {
        const container = document.querySelector('.container');
        if (!container) 
            return;
        const windowWidth = window.innerWidth,
            windowHeight = window.innerHeight;
        let containerWidth,
            containerHeight;
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

    $(window)
        .on('hashchange', router)
        .trigger('hashchange');

    adjustScale();
    $('body').css('visibility', 'visible');
    $(window).on('resize', adjustScale);

    $('.sMChang-link').on('click', function (e) {
        e.preventDefault();
        if (typeof isLoggedIn !== 'undefined' && isLoggedIn) {
            if (confirm("로그아웃 하시겠습니까?")) {
                window.location.href = 'logout.php';
            }
        } else {
            window.location.href = 'login.php';
        }
    });


});