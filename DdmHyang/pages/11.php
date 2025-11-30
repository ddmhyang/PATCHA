
        // -----------------------------------------------------------
        // 1. 전역 변수 및 유튜브 API 관련 함수 (가장 밖으로 꺼냄)
        // -----------------------------------------------------------
        var player;

        // 유튜브 API가 준비되면 자동으로 실행되는 함수
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('song', {
                events: {
                    'onReady': onPlayerReady
                }
            });
        }

        // 플레이어 로딩이 완료되면 실행되는 함수 (이제 밖으로 나와서 에러가 안 납니다!)
        function onPlayerReady(event) {
            const musicBtn = document.querySelector('.tBOff');
            
            if(musicBtn) {
                musicBtn.addEventListener('click', function() {
                    // 플레이어가 정상적으로 로딩되었는지 확인
                    if (player && typeof player.getPlayerState === 'function') {
                        var playerState = player.getPlayerState();

                        if (playerState == YT.PlayerState.PLAYING) {
                            player.pauseVideo();
                            // (옵션) 아이콘 모양을 바꾸려면 여기에 코드 추가
                        } else {
                            player.playVideo();
                            // (옵션) 아이콘 모양을 바꾸려면 여기에 코드 추가
                        }
                    }
                });
            }
        }

        // -----------------------------------------------------------
        // 2. 화면 크기 조절 함수 (Scale)
        // -----------------------------------------------------------
        function adjustScale() {
            const container = document.querySelector('.container');
            if (!container) return;
            
            let containerWidth, containerHeight;
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;

            // 모바일 vs 데스크탑 기준 설정
            if (windowWidth <= 768) {
                containerWidth = 720;
                containerHeight = 1280;
            } else {
                containerWidth = 1440;
                containerHeight = 900;
            }

            // 비율 계산
            const scale = Math.min(
                windowWidth / containerWidth,
                windowHeight / containerHeight
            );

            // 크기 적용
            container.style.transform = `scale(${scale})`;
            container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
            container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
        }

        // -----------------------------------------------------------
        // 3. 페이지 로드 및 이벤트 리스너 설정
        // -----------------------------------------------------------
        window.addEventListener('load', () => {
            adjustScale();
            document.body.style.visibility = 'visible'; // 화면 깜빡임 방지용
        });
        
        window.addEventListener('resize', adjustScale);

        // 유튜브 API 스크립트 강제 로드
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);


        // -----------------------------------------------------------
        // 4. JQuery 관련 로직 (Router 등)
        // -----------------------------------------------------------
        $(document).ready(function() {
            
            // 라우터 함수: URL의 # 뒤를 읽어서 페이지를 변경
            function router() {
                setTimeout(function() {
                    if(typeof hljs !== 'undefined') { // hljs가 있을 때만 실행 (에러 방지)
                        hljs.highlightAll();
                    }
                }, 100);

                const hash = window.location.hash.substring(2) || 'main_content';
                const [page, params] = hash.split('?');
                
                // 주의: chatOverlay, loadPage, updateSideNav 함수가
                // 다른 js 파일에 정의되어 있어야 에러가 안 납니다.
                if(typeof chatOverlay !== 'undefined') chatOverlay.hide();
                
                const url = `${page}.php${params ? '?' + params : ''}`;
                
                if(typeof loadPage === 'function') {
                    loadPage(url);
                } else {
                    console.warn("loadPage 함수가 없습니다. 페이지를 불러올 수 없습니다.");
                }
                
                if(typeof updateSideNav === 'function') updateSideNav(page); 
            }

            // 주소 변경 감지
            $(window).on('hashchange', router);
            
            // 처음 실행 시 라우터 가동
            router();

            // 채팅창 닫기 버튼 이벤트
            $(document).on('click', '#chat-overlay .chat-header', function() {
                if(typeof chatOverlay !== 'undefined') chatOverlay.hide();
                window.location.hash = '#/main_content';
            });
        });
    </script>