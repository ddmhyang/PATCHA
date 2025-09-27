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

window.addEventListener('load', () => {
    adjustScale();
document.body.style.visibility = 'visible';
});
window.addEventListener('resize', adjustScale);