document.addEventListener('DOMContentLoaded', function() {
    const content = document.getElementById('content');
    const navLinks = document.querySelectorAll('nav a[data-page]');

    function loadPage(pageUrl) {
        fetch(pageUrl)
            .then(response => response.text())
            .then(html => {
                content.innerHTML = html;
                window.history.pushState({ path: pageUrl }, '', pageUrl);
                updateActiveLink(pageUrl);
            })
            .catch(err => console.error('Failed to load page: ', err));
    }

    function updateActiveLink(url) {
        const urlParams = new URLSearchParams(url.split('?')[1]);
        const page = urlParams.get('page');

        navLinks.forEach(link => {
            const linkPage = new URLSearchParams(link.href.split('?')[1]).get('page');
            if (linkPage === page || (page === 'home' && linkPage.startsWith('page_view'))) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pageUrl = this.getAttribute('href');
            loadPage(pageUrl);
        });
    });

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.path) {
            loadPage(e.state.path);
        }
    });

    const initialPage = new URLSearchParams(window.location.search).get('page') || 'page_view&name=eden';
    loadPage(`main.php?page=${initialPage}`);
});

function adjustScale() {
    const container = document.querySelector('.container');
    if (!container) return;
    
    let containerWidth = 1440, containerHeight = 900;
    const windowWidth = window.innerWidth, windowHeight = window.innerHeight;
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
