function adjustScale() {
    const container = document.querySelector('.container');
    if (!container) return;
    const containerWidth = 1440, containerHeight = 900;
    const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
    const scale = Math.min(windowWidth / containerWidth, windowHeight / containerHeight);
    container.style.transform = `scale(${scale})`;
    container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
    container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
}

async function loadPage(pageName, params = '') {
    const contentArea = document.querySelector('.content');
    if (!contentArea) return;
    const pageFile = pageName + '.php' + params;
    try {
        const response = await fetch(pageFile);
        if (!response.ok) throw new Error(`'${pageFile}' 파일을 찾을 수 없습니다.`);
        const pageHtml = await response.text();
        contentArea.innerHTML = pageHtml;
        const scripts = contentArea.querySelectorAll('script');
        scripts.forEach(script => {
            const newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            document.body.appendChild(newScript).remove();
        });
    } catch (error) {
        console.error('페이지 로딩 중 오류:', error);
        contentArea.innerHTML = `<p style="color:red; text-align:center;">${error.message}</p>`;
    }
}

// "방송 수신기" 코드
$(document).on('navigate', function(e, data) {
    if (data && data.url) {
        handleNavigation(data.url);
    }
});

function handleNavigation(url) {
    if (url.includes('logout.php')) {
        window.location.href = url;
        return;
    }
    const urlObject = new URL(url, window.location.origin);
    const pageName = urlObject.searchParams.get('page') || 'main';
    const params = urlObject.search.substring(1).replace(`page=${pageName}`, '');
    const newPath = pageName + (params ? '&' + params : '');
    history.pushState({ path: newPath }, '', `#${newPath}`);
    route(newPath);
}

function route(path) {
    const [pageName, ...paramParts] = path.split('&');
    const params = paramParts.length > 0 ? '?' + paramParts.join('&') : '';
    const allowedPages = ['main', 'login', 'gallery', 'etc', 'gallery_upload', 'gallery_view', 'gallery_edit'];
    if (allowedPages.includes(pageName)) {
        loadPage(pageName, params);
    } else {
        loadPage('main');
    }
}

document.addEventListener('click', (e) => {
    const targetLink = e.target.closest('a');
    if (targetLink && targetLink.href.includes('index.php?page=')) {
        e.preventDefault();
        handleNavigation(targetLink.href);
    } else if (targetLink && targetLink.href.includes('logout.php')) {
        window.location.href = targetLink.href;
    }
});

window.addEventListener('popstate', (e) => {
    const path = e.state ? e.state.path : 'main';
    route(path);
});

window.addEventListener('load', () => {
    adjustScale();
    document.body.style.visibility = 'visible';
    const initialPath = window.location.hash ? window.location.hash.substring(1) : 'main';
    history.replaceState({ path: initialPath }, '', `#${initialPath}`);
    route(initialPath);
});

window.addEventListener('resize', adjustScale);


// 1. YouTube API 스크립트를 비동기적으로 로드합니다.
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// 2. API가 준비되면 이 함수(onYouTubeIframeAPIReady)를 자동으로 호출합니다.
var player; // player 변수를 전역에서 접근할 수 있도록 선언
function onYouTubeIframeAPIReady() {
    // 'song'이라는 id를 가진 iframe을 YouTube 플레이어로 만듭니다.
    player = new YT.Player('song', {
        events: {
            // 플레이어가 준비되면 onPlayerReady 함수를 실행합니다.
            'onReady': onPlayerReady 
        }
    });
}

// 3. 플레이어가 준비되었을 때 실행할 동작 (필요 시 사용)
function onPlayerReady(event) {
    // autoplay=1 파라미터가 있어서 자동으로 재생되므로 여기서는 특별한 동작이 필요 없습니다.
    // event.target.playVideo(); // 만약 자동 재생이 안되면 이 코드를 사용하세요.
}

// 4. .tBOff 버튼 클릭 이벤트를 설정합니다.
$(document).ready(function() {
    $('.tBOff').on('click', function() {
        // player 객체가 실제로 로드되었는지 확인합니다.
        if (player && typeof player.getPlayerState === 'function') {
            
            // 현재 플레이어의 상태를 가져옵니다 (재생중, 정지됨 등).
            var playerState = player.getPlayerState();
            
            // 만약 영상이 재생 중(YT.PlayerState.PLAYING)이라면,
            if (playerState == YT.PlayerState.PLAYING) {
                player.pauseVideo(); // 영상을 정지합니다.
            } 
            // 그렇지 않으면 (정지, 종료, 버퍼링 등 상태)
            else {
                player.playVideo(); // 영상을 재생합니다.
            }
        } else {
            console.error("YouTube 플레이어가 아직 준비되지 않았습니다.");
        }
    });
});