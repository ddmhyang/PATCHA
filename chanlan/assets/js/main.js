$(document).ready(function() {
    const contentContainer = $('#content');
    const chatOverlay = $('#chat-overlay');

    function loadPage(url) {
        $.ajax({
            url: url, type: 'GET',
            success: (response) => contentContainer.html(response),
            error: () => contentContainer.html('<h1>페이지를 불러올 수 없습니다.</h1>')
        });
    }
    
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

        const windowWidth = window.innerWidth;
        if (windowWidth > 784) {
            if (visiblePages.includes(currentPage)) {
                nav1.css('width', '65px');
                nav2.css('width', '65px');
                nav3.css('width', '65px');

                if (currentPage === 'chanlan') {
                    nav1.css('width', '82px');
                } else if (currentPage === 'hyun') {
                    nav2.css('width', '82px');
                } else if (currentPage === 'chan') {
                    nav3.css('width', '82px');
                }

                navContainer.show();
            } else {
                navContainer.hide();
            }

            ///////////////////////////////
            if (visiblePages2.includes(currentPage)) {
                nav4.css('width', '65px');
                nav5.css('width', '65px');

                if (currentPage === 'gallery') {
                    nav4.css('width', '82px');
                } else if (currentPage === 'trpg') {
                    nav5.css('width', '82px');
                }

                navGallery.show(); 
            } else {
                navGallery.hide();
            }

            if (currentPage === 'settings') {
                indexPanel1.css('transform', 'scaleY(1.2)');
            } else {
            }
            
        } else {
            if (visiblePages.includes(currentPage)) {
                nav1.css('width', '70px');
                nav1.css('height', '70px');
                nav2.css('width', '70px');
                nav2.css('height', '70px');
                nav3.css('width', '70px');
                nav3.css('height', '70px');

                if (currentPage === 'chanlan') {
                    nav1.css('width', '80px');
                    nav1.css('height', '80px');
                } else if (currentPage === 'hyun') {
                    nav2.css('width', '80px');
                    nav2.css('height', '80px');
                } else if (currentPage === 'chan') {
                    nav3.css('width', '80px');
                    nav3.css('height', '80px');
                }

                navContainer.show(); 
            } else {
                navContainer.hide(); 
            }

            ///////////////////////////////
            if (visiblePages2.includes(currentPage)) {
                nav4.css('width', '70px');
                nav4.css('height', '70px');
                nav5.css('width', '70px');
                nav5.css('height', '70px');

                if (currentPage === 'gallery') {
                    nav4.css('width', '80px');
                    nav4.css('height', '80px');
                } else if (currentPage === 'trpg') {
                    nav5.css('width', '80px');
                    nav5.css('height', '80px');
                }

                navGallery.show();
            } else {
                navGallery.hide();
            }

            if (currentPage === 'settings') {
                indexPanel1.css('transform', 'scale(1.1)');
            } else {
            }
        }
    }

    function router() {
        const hash = window.location.hash.substring(2) || 'main_content';
        const [page, params] = hash.split('?');
        
        if (page === 'chat') {
            contentContainer.html('');
            loadChat();
        } else {
            chatOverlay.hide();
            const url = `${page}.php${params ? '?' + params : ''}`;
            loadPage(url);
        }
        
        updateSideNav(page); 
    }


    $(window).on('hashchange', router);
    router();

    $(document).on('click', '#chat-overlay .chat-header', function() {
        chatOverlay.hide();
        window.location.hash = '#/main_content';
    });

    $(document).on('click', '.logo', function() {
        window.location.href = '../index.php';
    });


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





    $(document).on('wheel', '.timeline-wrapper', function(e) {
        e.preventDefault();
        this.scrollLeft += e.originalEvent.deltaY;
    });


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
            processData: false,
            contentType: false,
            dataType: 'json',
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
    
    const musicPlayer = document.getElementById('music-player');
    const playButton = $('.index_panel2');

    if (musicPlayer.paused) {
        playButton.removeClass('playing');
    } else {
        playButton.addClass('playing');
    }

    playButton.on('click', function() {
        if (musicPlayer.paused) {
            musicPlayer.play();
            $(this).addClass('playing');
        } else {
            musicPlayer.pause();
            $(this).removeClass('playing');
        }
    });

    $(musicPlayer).on('ended', function() {
        playButton.removeClass('playing');
    });
    
    $(document).one('click', function() {
        if (musicPlayer.paused) {
            musicPlayer.play().then(() => {
                playButton.addClass('playing');
            }).catch(error => {
            });
        }
    });

    $(document).on('click', '.mobile_menu, .mobile_sub_menu_overlay', function() {
        const subMenu = $('.mobile_sub_menu');
        const overlay = $('.mobile_sub_menu_overlay');
        const panels = $('.index_panel1, .index_panel2, .chanlan_nav_container, .gallery_nav_container');

        if (subMenu.hasClass('show')) {
            subMenu.removeClass('show');
            overlay.removeClass('show');
            panels.removeClass('visible');
        } else {
            subMenu.addClass('show');
            overlay.addClass('show');

            setTimeout(function() {
                panels.addClass('visible');
            }, 500);
        }
    });
    $(document).on('click', '.index_panel1', function() {
        window.location.hash = '#/settings';
    });
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

window.addEventListener('load', () => {
    adjustScale();
    document.body.style.visibility = 'visible';
});
window.addEventListener('resize', adjustScale);