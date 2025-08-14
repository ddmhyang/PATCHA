$(document).ready(function() {
    const contentContainer = $('#content');
    const chatOverlay = $('#chat-overlay'); // 채팅 오버레이 div 선택

    function loadPage(url) {
        $.ajax({
            url: url, type: 'GET',
            success: (response) => contentContainer.html(response),
            error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
        });
    }
    
    // 채팅창을 불러오는 별도의 함수
    function loadChat() {
        $.ajax({
            url: 'chat.php',
            type: 'GET',
            success: function(response) {
                chatOverlay.html(response).show();
            }
        });
    }

        function updateSideNav(currentPage) {
        const navContainer = $('.chanlan_nav_container');
        const nav1 = $('.chanlan_nav1');
        const nav2 = $('.chanlan_nav2');
        const nav3 = $('.chanlan_nav3');
        const navGallery = $('.gallery_nav_container');
        const nav4 = $('.gallery_nav');
        const nav5 = $('.trpg_nav');
        const indexPanel1 = $('.index_panel1');


        ///////////////////////////////

        const visiblePages = ['chanlan', 'chan', 'hyun'];
        const visiblePages2 = ['gallery', 'trpg'];

        if (visiblePages.includes(currentPage)) {
            // 일단 기본 너비로 초기화
            nav1.css('width', '65px');
            nav2.css('width', '65px');
            nav3.css('width', '65px');

            // 현재 페이지에 따라 특정 div의 너비만 90px로 변경
            if (currentPage === 'chanlan') {
                nav1.css('width', '82px');
            } else if (currentPage === 'hyun') {
                nav2.css('width', '82px');
            } else if (currentPage === 'chan') {
                nav3.css('width', '82px');
            }

            navContainer.show(); // 컨테이너를 보여줌
        } else {
            navContainer.hide(); // 그 외 페이지에서는 컨테이너를 숨김
        }

        ///////////////////////////////
        if (visiblePages2.includes(currentPage)) {
            // 일단 기본 너비로 초기화
            nav4.css('width', '65px');
            nav5.css('width', '65px');

            // 현재 페이지에 따라 특정 div의 너비만 90px로 변경
            if (currentPage === 'gallery') {
                nav4.css('width', '82px');
            } else if (currentPage === 'trpg') {
                nav5.css('width', '82px');
            }

            navGallery.show(); // 컨테이너를 보여줌
        } else {
            navGallery.hide(); // 그 외 페이지에서는 컨테이너를 숨김
        }

        if (currentPage === 'settings') {
            indexPanel1.css('transform', 'scaleY(1.2)');
        } else {
            // 다른 페이지일 경우 스타일 제거
        }
    }

    function router() {
        const hash = window.location.hash.substring(2) || 'main_content';
        const [page, params] = hash.split('?');
        
        // ★★★ 라우터 로직 수정 ★★★
        if (page === 'chat') {
            // 'chat'일 경우, 메인 콘텐츠를 비우고 채팅 오버레이를 로드
            contentContainer.html(''); // 다른 페이지가 보이지 않도록 비움
            loadChat();
        } else {
            // 그 외의 경우, 채팅 오버레이를 숨기고 메인 콘텐츠를 로드
            chatOverlay.hide();
            const url = `${page}.php${params ? '?' + params : ''}`;
            loadPage(url);
        }
        
        // 사이드 내비게이션 업데이트
        updateSideNav(page); 
    }


    $(window).on('hashchange', router);
    router();

    $(document).on('click', '#chat-overlay .chat-header', function() {
        chatOverlay.hide();
        // 채팅창을 닫으면 메인 페이지로 이동
        window.location.hash = '#/main_content';
    });

    $(document).on('click', '.logo', function() {
        window.location.href = '../index.php';
    });


        // ▼▼▼ 이 부분이 추가되었습니다. ▼▼▼
    // 사이드 내비게이션 클릭 이벤트
    $(document).on('click', '.chanlan_nav1', function() {
        window.location.hash = '#/chanlan';
    });

    $(document).on('click', '.chanlan_nav2', function() {
        window.location.hash = '#/hyun';
    });

    $(document).on('click', '.chanlan_nav3', function() {
        window.location.hash = '#/chan';
    });
    $(document).on('click', '.gallery_nav', function() {
        window.location.hash = '#/gallery';
    });

    $(document).on('click', '.trpg_nav', function() {
        window.location.hash = '#/trpg';
    });
    // ▲▲▲ 여기까지 추가 ▲▲▲

    // Summernote 이미지 업로드를 위한 전역 함수 (오류 수정)
    window.uploadSummernoteImage = function(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.url) {
                    // editor.summernote('insertImage', response.url); // 이 방식이 오류를 유발할 수 있음
                    $(editor).summernote('insertImage', response.url); // editor를 jQuery 객체로 감싸서 호출
                } else {
                    alert('이미지 업로드 실패: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    };

    // 공통 AJAX 폼 제출 처리
    $(document).on('submit', 'form.ajax-form', function(e) {
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
            success: function(response) {
                if (response.redirect_url === 'reload') {
                    window.location.reload(); // 'reload' 신호를 받으면 페이지 전체를 새로고침
                    return;
                }
                if (response.success) {
                    alert('성공적으로 처리되었습니다.');
                    if (response.redirect_url) {
                        window.location.hash = response.redirect_url;
                    } else {
                        router(); // 페이지 새로고침
                    }
                } else {
                    alert('오류: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('요청 처리 중 오류가 발생했습니다.')
        });
    });

    // 삭제 버튼 공통 처리
    $(document).on('click', '.delete-btn', function() {
        if (!confirm('정말로 삭제하시겠습니까?')) return;

        const id = $(this).data('id');
        const type = $(this).data('type');

        $.ajax({
            url: 'ajax_delete_gallery.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('삭제되었습니다.');
                    window.location.hash = `#/${type}`;
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
             error: () => alert('삭제 요청 중 서버 오류가 발생했습니다.')
        });
    });
    
        // ▼▼▼ 음악 플레이어 로직 (index_panel2 사용) ▼▼▼
    const musicPlayer = document.getElementById('music-player');
    const playButton = $('.index_panel2'); // 버튼을 .index_panel2로 변경

    // 초기 상태 설정
    if (musicPlayer.paused) {
        playButton.removeClass('playing');
    } else {
        playButton.addClass('playing');
    }

    // 버튼 클릭 이벤트
    playButton.on('click', function() {
        if (musicPlayer.paused) {
            musicPlayer.play();
            $(this).addClass('playing'); // playing 클래스 추가
        } else {
            musicPlayer.pause();
            $(this).removeClass('playing'); // playing 클래스 제거
        }
    });

    // 음악이 끝나면 버튼 상태 초기화 (loop 속성 사용 시 필요 없음)
    $(musicPlayer).on('ended', function() {
        playButton.removeClass('playing');
    });
    
    // 자동재생이 막혔을 경우, 첫 클릭 시 재생 시도
    $(document).one('click', function() {
        if (musicPlayer.paused) {
            musicPlayer.play().then(() => {
                playButton.addClass('playing');
            }).catch(error => {
                // 사용자가 상호작용하기 전에 재생할 수 없다는 오류는 무시
            });
        }
    });
    // ▲▲▲ 음악 플레이어 로직 끝 ▲▲▲

    // .index_panel1 클릭 시 설정 페이지로 이동
    $(document).on('click', '.index_panel1', function() {
        window.location.hash = '#/settings';
    });
});

function adjustScale() {
    // 대상을 .entry-container가 아닌 .container로 변경
    const container = document.querySelector('.container');
    if (!container) 
        return;
    
    let containerWidth,
        containerHeight;
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