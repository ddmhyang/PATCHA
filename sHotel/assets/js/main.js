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

async function loadPage(url) {
    const cleanUrl = url.replace('#!', '');
    try {
        const response = await fetch(cleanUrl);
        if (!response.ok) throw new Error('Page load failed.');
        const pageHtml = await response.text();
        const contentArea = document.querySelector('.content');
        if (contentArea) {
            contentArea.innerHTML = pageHtml;
        }
    } catch (error) {
        console.error('Failed to load page: ', error);
    }
}

function navigateTo(url) {
    history.pushState({ path: url }, '', `#${url.replace('#!', '')}`);
    loadPage(url);
}

document.addEventListener('click', (e) => {
    const targetLink = e.target.closest('.nav_box a'); 
    if (targetLink) {
    e.preventDefault();
        const url = targetLink.getAttribute('href');
        navigateTo(url);
    }
});

window.addEventListener('popstate', (e) => {
    const path = e.state ? e.state.path : 'main.php';
    loadPage(path);
});

window.addEventListener('load', () => {
    const path = window.location.hash ? window.location.hash.substring(1) : 'main.php';
    loadPage(path);
    history.replaceState({ path: path }, '', `#${path}`);
    adjustScale();
    document.body.style.visibility = 'visible';
});

window.addEventListener('resize', adjustScale);