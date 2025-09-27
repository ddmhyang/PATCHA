function adjustScale() {
    const container = document.querySelector('.container');
    if (!container) return;
    
    let containerWidth, containerHeight;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    // if (windowWidth <= 768) {
    //     containerWidth = 720;
    //     containerHeight = 1280;
    // } else {
        containerWidth = 1440;
        containerHeight = 900;
    // }

    const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
    container.style.transform = `scale(${scale})`;
    container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
    container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
}

// 페이지를 불러오고, 그 안의 스크립트까지 실행하는 새로운 함수
async function loadPage(url) {
    // #! 같은 불필요한 부분을 제거합니다.
    
    try {
        const response = await fetch(cleanUrl);
        if (!response.ok) throw new Error('페이지 로드 실패');
        
        const pageHtml = await response.text();
        const contentArea = document.querySelector('.content');
        
        if (contentArea) {
            // 1. 가져온 HTML을 화면에 끼워 넣습니다.
            contentArea.innerHTML = pageHtml;

            // 2. 끼워 넣은 HTML 안에서 script 태그를 모두 찾습니다.
            const scripts = contentArea.querySelectorAll('script');

            // 3. 찾은 script들을 하나씩 실행시켜 줍니다.
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                // src 속성이 있으면 그대로 복사하고, 없으면 내부 코드를 복사합니다.
                if (script.src) {
                    newScript.src = script.src;
                } else {
                    newScript.innerHTML = script.innerHTML;
                }
                document.body.appendChild(newScript).remove();
            });
        }
    } catch (error) {
        console.error('페이지 로딩 중 오류 발생: ', error);
    }
}

// SPA 내비게이션 기능 (수정 없음)
function navigateTo(url) {
    history.pushState({ path: url }, '', `#${url}`);
    loadPage(url);
}

// SPA 링크 클릭 처리 (수정 없음)
document.addEventListener('click', (e) => {
    // nav 링크 클릭 시에만 작동하도록 수정
    const targetLink = e.target.closest('header nav a');
    if (targetLink) {
        e.preventDefault();
        const url = targetLink.getAttribute('href');
        navigateTo(url);
    }
});

// 뒤로가기/앞으로가기 버튼 처리 (수정 없음)
window.addEventListener('popstate', (e) => {
    const path = e.state ? e.state.path : 'index.php?page=main';
    loadPage(path);
});

// 페이지 첫 로드 시 처리
window.addEventListener('load', () => {
    const path = window.location.hash ? window.location.hash.substring(1) : 'main.php';
    loadPage(path);
    history.replaceState({ path: path }, '', `#${path}`);
    adjustScale();
    document.body.style.visibility = 'visible';
});


window.addEventListener('resize', adjustScale);