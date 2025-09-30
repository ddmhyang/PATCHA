$(document).ready(function() {
    const contentContainer = $('#content');
    //채팅창이 있을 경우 아래 포함
    const chatOverlay = $('#chat-overlay');

    function loadPage(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: (response) => contentContainer.html(response),
            error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
        });
    }
    
    // 채팅창 내용 불러오는 함수.
    function loadChat() {
        $.ajax({
            url: 'chat.php', // 채팅 내용을 담고 있는 chat.php 파일에 요청
            type: 'GET',
            success: function(response) {
                chatOverlay.html(response).show();
            }
        });
    }

    // 주소창의 해시(#) 값에 따라 적절한 페이지를 불러오는 라우터(경로 설정) 함수입니다.
    function router() {
        const hash = window.location.hash.substring(2) || 'main_content';
        // '?'를 기준으로 페이지 이름과 URL 파라미터(예: 'id=3')를 분리합니다.
        const [page, params] = hash.split('?');
        
        // 만약 요청된 페이지가 'chat'이라면
        if (page === 'chat') {
            contentContainer.html(''); // 메인 콘텐츠 영역은 비우고
            loadChat();                // 채팅창을 불러옵니다.
        } else { // 그 외의 모든 페이지라면
            chatOverlay.hide(); // 채팅창은 숨기고
            // 'gallery_view.php?id=3'과 같은 최종 URL 문자열을 만듭니다.
            const url = `${page}.php${params ? '?' + params : ''}`;
            loadPage(url); // 만든 URL로 페이지 내용을 불러옵니다.
        }
        
        // 페이지가 변경되었으므로, 측면 네비게이션 스타일도 업데이트합니다.
        updateSideNav(page); 
    }

    // $(window).on('hashchange', router);
    // 브라우저의 '뒤로가기'/'앞으로가기' 버튼을 누르거나 링크를 클릭해 해시가 변경될 때마다 router 함수를 실행합니다.
    $(window).on('hashchange', router);
    // 페이지가 처음 로드될 때도 router 함수를 한 번 실행하여 초기 화면을 표시합니다.
    router();

    // $(document).on('click', selector, function() { ... });
    // 동적으로 생성된 요소에도 이벤트를 바인딩하기 위한 구문입니다.
    // 채팅창 헤더를 클릭하면 채팅창을 숨기고, 주소를 메인 콘텐츠로 변경합니다.
    $(document).on('click', '#chat-overlay .chat-header', function() {
        chatOverlay.hide();
        window.location.hash = '#/main_content';
    });

    // 로고를 클릭하면 초기 index.php로 이동합니다.
    $(document).on('click', '.logo', function() {
        window.location.href = '../index.php';
    });

    // 각 네비게이션 버튼 클릭 시, 해당하는 페이지의 해시로 주소를 변경합니다.
    $(document).on('click', '.chanlan_nav1', function() { window.location.hash = '#/chanlan'; });
    $(document).on('click', '.chanlan_nav2', function() { window.location.hash = '#/hyun'; });
    // ... (다른 네비게이션 클릭 이벤트 생략) ...

    // 타임라인 컨테이너에서 마우스 휠을 스크롤하면, 가로로 스크롤되도록 이벤트를 설정합니다.
    $(document).on('wheel', '.timeline-wrapper', function(e) {
        e.preventDefault(); // 기본 세로 스크롤 동작을 막습니다.
        this.scrollLeft += e.originalEvent.deltaY; // 휠의 Y축 움직임을 가로 스크롤 값에 더합니다.
    });

    // Summernote 편집기에서 이미지를 업로드할 때 호출될 전역 함수를 정의합니다.
    window.uploadSummernoteImage = function(file, editor) {
        let data = new FormData(); // 파일을 담을 FormData 객체를 생성합니다.
        data.append("file", file);   // 'file'이라는 이름으로 실제 이미지 파일을 추가합니다.
        $.ajax({
            url: 'ajax_upload_image.php', // 이미지 업로드 처리 파일
            type: "POST", data: data,
            contentType: false, processData: false, dataType: 'json', // 파일 전송 시 필수적인 설정입니다.
            success: function(response) {
                // 업로드가 성공하고 서버가 이미지 URL을 보내주면
                if (response.success && response.url) {
                    // Summernote의 내장 함수를 사용해 편집기에 해당 이미지를 삽입합니다.
                    $(editor).summernote('insertImage', response.url);
                } else {
                    alert('이미지 업로드 실패: ' + (response.message || '알 수 없는 오류'));
                }
            },
            error: () => alert('이미지 업로드 중 서버 오류가 발생했습니다.')
        });
    };

    // <form class="ajax-form"> 형태를 가진 모든 폼의 'submit'(제출) 이벤트가 발생하면 이 함수를 실행합니다.
    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault(); // 폼의 기본 동작(페이지 새로고침)을 막습니다.
        const form = $(this);
        const formData = new FormData(this); // 폼 안의 모든 입력 데이터를 FormData 객체로 만듭니다.

        // 만약 폼 안에 Summernote 편집기가 있다면,
        if (form.find('.summernote').length) {
            // 편집기 안의 모든 HTML 내용을 'content'라는 이름으로 FormData에 덮어씁니다.
            formData.set('content', form.find('.summernote').summernote('code'));
        }

        $.ajax({
            url: form.attr('action'), // 폼의 'action' 속성에 지정된 URL로 전송합니다.
            type: 'POST',
            data: formData, 
            processData: false, contentType: false, dataType: 'json',
            success: (response) => {
                // 서버로부터 성공 응답을 받으면
                if (response.success) {
                    alert(response.message || '성공적으로 처리되었습니다.');
                    // 서버가 'reload'를 지시하면 페이지를 새로고침합니다.
                    if (response.redirect_url === 'reload') {
                        window.location.reload();
                    // 이동할 #주소를 알려주면 그곳으로 이동합니다.
                    } else if (response.redirect_url) {
                        window.location.hash = response.redirect_url;
                    } else {
                        // 별다른 지시가 없으면 현재 페이지를 다시 로드합니다.
                        router();
                    }
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: () => alert('요청 처리 중 오류가 발생했습니다.')
        });
    });
    
    // <button class="delete-btn"> 을 클릭하면 실행됩니다.
    $(document).on('click', '.delete-btn', function() {
        // 정말 삭제할 것인지 사용자에게 확인을 받습니다.
        if (!confirm('정말로 삭제하시겠습니까?')) return;

        // 버튼의 'data-id'와 'data-type' 속성에 저장된 값을 가져옵니다.
        const id = $(this).data('id');
        const type = $(this).data('type');

        $.ajax({
            url: 'ajax_delete_gallery.php', // 갤러리 삭제 처리 파일
            type: 'POST',
            data: { id: id }, // 삭제할 게시물의 ID를 전송합니다.
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert('삭제되었습니다.');
                    // 삭제 성공 후, 해당 게시물이 있던 목록 페이지로 이동합니다.
                    window.location.hash = `#/${type}`;
                } else {
                    alert('삭제 실패: ' + response.message);
                }
            },
            error: function() {
                alert('삭제 요청 중 서버 오류가 발생했습니다.');
            }
        });
    });
    
    // 음악 플레이어와 재생 버튼 요소를 가져옵니다.
    const musicPlayer = document.getElementById('music-player');
    const playButton = $('.index_panel2');

    // 페이지 로드 시, 음악이 재생 중이면 버튼에 'playing' 클래스를 추가하고, 아니면 제거합니다.
    if (musicPlayer.paused) {
        playButton.removeClass('playing');
    } else {
        playButton.addClass('playing');
    }

    // 재생 버튼을 클릭했을 때의 동작입니다.
    playButton.on('click', function() {
        if (musicPlayer.paused) { // 음악이 멈춰있으면
            musicPlayer.play();     // 재생하고
            $(this).addClass('playing'); // 'playing' 클래스 추가
        } else { // 재생 중이면
            musicPlayer.pause();    // 멈추고
            $(this).removeClass('playing'); // 'playing' 클래스 제거
        }
    });

    // 음악 재생이 끝나면 'playing' 클래스를 제거합니다.
    $(musicPlayer).on('ended', function() {
        playButton.removeClass('playing');
    });
    
    // 사용자가 페이지와 처음 상호작용(클릭)할 때, 음악을 자동 재생하도록 시도합니다. (브라우저 정책)
    $(document).one('click', function() {
        if (musicPlayer.paused) {
            musicPlayer.play().then(() => {
                playButton.addClass('playing');
            }).catch(error => {
                // 자동 재생 실패 시 (사용자가 상호작용하기 전 등)
            });
        }
    });

    // 모바일 메뉴 버튼이나 오버레이를 클릭했을 때의 동작입니다.
    $(document).on('click', '.mobile_menu, .mobile_sub_menu_overlay', function() {
        const subMenu = $('.mobile_sub_menu');
        const overlay = $('.mobile_sub_menu_overlay');
        const panels = $('.index_panel1, .index_panel2, .chanlan_nav_container, .gallery_nav_container');

        // 메뉴가 이미 열려있으면 닫고,
        if (subMenu.hasClass('show')) {
            subMenu.removeClass('show');
            overlay.removeClass('show');
            panels.removeClass('visible');
        } else { // 닫혀있으면 엽니다.
            subMenu.addClass('show');
            overlay.addClass('show');

            // 0.5초 후에 메뉴 아이콘들이 나타나도록 애니메이션 효과를 줍니다.
            setTimeout(function() {
                panels.addClass('visible');
            }, 500);
        }
    });
    
    // 설정 패널 아이콘을 클릭하면 설정 페이지로 이동합니다.
    $(document).on('click', '.index_panel1', function() {
        window.location.hash = '#/settings';
    });
});

function adjustScale() {
    const container = document.querySelector('.container');
    if (!container) return;
    const windowWidth = window.innerWidth,
          windowHeight = window.innerHeight;
    let containerWidth, containerHeight;
    if (windowWidth <= 784) {
        containerWidth = 720;
        containerHeight = 1280;
    } else {
        containerWidth = 1440;
        containerHeight = 900;
    }
    const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
    container.style.transform = `scale(${scale})`;
    container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
    container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
}

window.addEventListener('load', () => {
    adjustScale();
    document.body.style.visibility = 'visible';
});
window.addEventListener('resize', adjustScale);