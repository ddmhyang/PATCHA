$(document).ready(function() {
    const contentContainer = $('#content');
    const chatOverlay = $('#chat-overlay');

    function applyKoreanStyles() {
        // 사용자가 요청한 모든 컨테이너 선택자를 콤마(,)로 연결합니다.
        const targetSelector = `
            .login-container, .form-page-container, .password-form-container, 
            .view-container, .gallery-container, .page-container, .settings-container
        `;
        
        // 위 선택자에 해당하는 요소를 모두 찾습니다.
        const elements = document.querySelectorAll(targetSelector);

        elements.forEach(element => {
            // 자식 요소들까지 모두 순회하며 텍스트 노드만 찾습니다.
            const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, null, false);
            let node;
            while(node = walker.nextNode()) {
                // 빈 텍스트 노드는 건너뜁니다.
                if (node.nodeValue.trim() === '') continue;
                // 부모가 스크립트나 스타일 태그인 경우 건너뜁니다.
                if (['SCRIPT', 'STYLE'].includes(node.parentNode.tagName)) continue;

                const koreanRegex = /([가-힣]+)/g;
                if (koreanRegex.test(node.nodeValue)) {
                    // 임시 div를 사용해 HTML 문자열을 실제 노드로 변환합니다.
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = node.nodeValue.replace(koreanRegex, '<span class="korean-text">$1</span>');
                    
                    // 생성된 노드들을 원래 텍스트 노드 앞에 삽입합니다.
                    while (tempDiv.firstChild) {
                        node.parentNode.insertBefore(tempDiv.firstChild, node);
                    }
                    // 원래 텍스트 노드를 제거합니다.
                    node.parentNode.removeChild(node);
                }
            }
        });
    }
    function loadPage(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: (response) => {
                contentContainer.html(response);
                applyKoreanStyles();
            },
            error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
        });
    }


    function router() {
        const hash = window.location.hash.substring(2) || 'main_content';
        const [page, params] = hash.split('?');
    }

    $(window).on('hashchange', router);
    router();


    window.uploadSummernoteImage = function(file, editor) {
        let data = new FormData();
        data.append("file", file);
        $.ajax({
            url: 'ajax_upload_image.php',
            type: "POST", data: data,
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
    };

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
            processData: false, contentType: false, dataType: 'json',
            success: (response) => {
                if (response.success) {
                    alert(response.message || '성공적으로 처리되었습니다.');
                    if (response.redirect_url === 'reload') {
                        window.location.reload();
                    } else if (response.redirect_url) {
                        window.location.hash = response.redirect_url;
                    } else {
                        router();
                    }
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: () => alert('요청 처리 중 오류가 발생했습니다.')
        });
    });
    
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
            error: function() {
                alert('삭제 요청 중 서버 오류가 발생했습니다.');
            }
        });
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
window.addEventListener('resize', adjustScale)
