<?php
// index.php : 메인 화면 (반응형 풀스크린 + 결투 알림 + 로그 통합)
session_start();

if (!file_exists('config.php')) { header("Location: setup.php"); exit; }
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$is_login = isset($_SESSION['uid']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>School Survival</title>
        <link
            rel="stylesheet"
            as="style"
            crossorigin="crossorigin"
            href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css"/>
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <style>
            :root {
                --primary: #CE5961;
                --secondary: #D67F85;
                --point: #AED1D5;
                --bg: #F0F2F5;
                --text: #333;
                --white: #fff;
            }
            body {
                font-family: 'Pretendard', sans-serif;
                background: var(--bg);
                color: var(--text);
                margin: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            /* 헤더 스타일 */
            header {
                background: var(--primary);
                color: var(--white);
                padding: 15px 20px;
                font-weight: 800;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 15px rgba(206, 89, 97, 0.2);
                position: sticky;
                top: 0;
                z-index: 100;
            }
            header .brand {
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 18px;
            }
            header .logout {
                font-size: 13px;
                background: rgba(0,0,0,0.1);
                padding: 5px 12px;
                border-radius: 20px;
                cursor: pointer;
            }

            .container {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                box-sizing: border-box;
                flex: 1;
            }

            /* 로그인 박스 */
            .login-wrapper {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 80vh;
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                width: 100%;
                max-width: 350px;
                text-align: center;
            }
            .login-logo {
                font-size: 50px;
                color: var(--primary);
                margin-bottom: 20px;
            }
            input {
                width: 100%;
                padding: 15px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
                border-radius: 12px;
                box-sizing: border-box;
            }
            .btn-main {
                width: 100%;
                padding: 15px;
                background: var(--primary);
                color: white;
                border: none;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 700;
                cursor: pointer;
            }

            /* 프로필 카드 */
            .profile-card {
                background: linear-gradient(135deg, var(--secondary), var(--primary));
                color: white;
                padding: 25px;
                border-radius: 20px;
                box-shadow: 0 10px 20px rgba(206, 89, 97, 0.2);
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .profile-info h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 800;
            }
            .profile-info p {
                margin: 5px 0;
                opacity: 0.9;
                font-size: 15px;
            }
            .profile-avatar {
                width: 60px;
                height: 60px;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
            }

            /* 부상 배지 */
            .injury-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: bold;
                margin-top: 5px;
            }
            .inj-0 {
                background: #2ecc71;
                color: white;
            }
            .inj-1 {
                background: #f1c40f;
                color: #000;
            }
            .inj-2 {
                background: #e67e22;
                color: white;
            }
            .inj-3 {
                background: #e74c3c;
                color: white;
            }
            .inj-4 {
                background: #000;
                color: red;
                border: 1px solid red;
            }

            /* 대시보드 그리드 */
            .dashboard-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .menu-card {
                background: white;
                border-radius: 18px;
                padding: 25px 15px;
                text-align: center;
                box-shadow: 0 4px 10px rgba(0,0,0,0.02);
                cursor: pointer;
                transition: 0.2s;
                border: 2px solid transparent;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            .menu-card:hover {
                transform: translateY(-5px);
                border-color: var(--point);
            }
            .menu-card i {
                font-size: 32px;
                margin-bottom: 12px;
                color: var(--primary);
            }
            .menu-card span {
                font-weight: 700;
                font-size: 17px;
            }
            .menu-card .sub {
                font-size: 12px;
                color: #999;
                margin-top: 4px;
            }

            /* 결투 알림 */
            #challenge-alert {
                display: none;
                background: #e74c3c;
                color: white;
                padding: 12px;
                text-align: center;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1001;
                font-weight: bold;
                animation: blink 1s infinite;
            }
            @keyframes blink {
                50% {
                    opacity: 0.7;
                }
            }

            /* 모달 스타일 */
            .modal-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.7);
                z-index: 1000;
                justify-content: center;
                align-items: center;
            }
            .modal-content {
                background: white;
                padding: 25px;
                border-radius: 20px;
                width: 90%;
                max-width: 350px;
                text-align: center;
            }
            .btn-modal {
                width: 100%;
                padding: 15px;
                margin-bottom: 10px;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                font-weight: bold;
            }

            .user-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px;
                border-bottom: 1px solid #eee;
            }
        </style>
    </head>
    <body>

        <?php if (!$is_login): ?>
        <div class="login-wrapper">
            <div class="login-box">
                <div class="login-logo">
                    <i class="fa-solid fa-school"></i>
                </div>
                <div
                    class="login-title"
                    style="font-size:22px; font-weight:800; margin-bottom:20px;">졸업하게 해주세요!</div>
                <input type="text" id="login-name" placeholder="이름">
                <input type="password" id="login-pw" placeholder="비밀번호">
                <button class="btn-main" onclick="App.login()">접속하기</button>
            </div>
        </div>
    <?php else: ?>
        <div id="challenge-alert">
            ⚔️
            <span id="chal-sender"></span>님의 결투 신청!
            <button
                onclick="App.acceptChallenge()"
                style="margin-left:10px; background:white; color:red; border:none; padding:4px 10px; border-radius:5px; font-weight:bold; cursor:pointer;">수락</button>
        </div>

        <header>
            <div class="brand">
                <i class="fa-solid fa-graduation-cap"></i>
                졸업하게 해주세요!</div>
            <div class="logout" onclick="location.href='index.php?logout=1'">
                <i class="fa-solid fa-right-from-bracket"></i>
                로그아웃</div>
        </header>

        <div class="container">
            <div class="profile-card">
                <div class="profile-info">
                    <h1 id="ui-name">불러오는 중...</h1>
                    <p id="ui-stat">데이터 동기화 중...</p>
                    <div id="ui-injury"></div>
                </div>
                <div class="profile-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>

            <?php if ($role === 'admin'): ?>
            <div
                style="font-size:14px; font-weight:bold; color:#777; margin-bottom:10px; margin-left:5px;">관리자 제어 패널</div>
            <div class="dashboard-grid">
                <div class="menu-card" onclick="location.href='admin_member.php'">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>캐릭터 관리</span><span class="sub">생성/수정/삭제</span></div>
                <div class="menu-card" onclick="location.href='admin_item.php'">
                    <i class="fa-solid fa-shirt"></i>
                    <span>아이템 설정</span><span class="sub">상점 및 장비</span></div>
                <div class="menu-card" onclick="location.href='admin_monster.php'">
                    <i class="fa-solid fa-skull-crossbones"></i>
                    <span>몬스터 설정</span><span class="sub">스텟 및 드랍</span></div>
                <div class="menu-card" onclick="location.href='admin_status.php'">
                    <i class="fa-solid fa-flask"></i>
                    <span>상태이상</span><span class="sub">효과 데이터</span></div>
                <div class="menu-card" onclick="location.href='admin_gamble.php'">
                    <i class="fa-solid fa-dice"></i>
                    <span>도박장</span><span class="sub">확률 조정</span></div>
                <div class="menu-card" onclick="location.href='admin_battle.php'">
                    <i class="fa-solid fa-server"></i>
                    <span>방 관리</span><span class="sub">전투 세션</span></div>
                <div class="menu-card" onclick="location.href='log.php'">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>전체 로그</span><span class="sub">서버 기록</span></div>
            </div>
        <?php else: ?>
            <div
                style="font-size:14px; font-weight:bold; color:#777; margin-bottom:10px; margin-left:5px;">학교 생활</div>
            <div class="dashboard-grid">
                <div class="menu-card" onclick="App.openBattleModal()">
                    <i class="fa-solid fa-hand-fist"></i>
                    <span>싸움</span><span class="sub">탐색 또는 다툼</span></div>
                <div class="menu-card" onclick="location.href='inventory.php'">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>가방</span><span class="sub">내 소지품</span></div>
                <div class="menu-card" onclick="location.href='shop.php'">
                    <i class="fa-solid fa-shop"></i>
                    <span>매점</span><span class="sub">아이템 구매</span></div>
                <div class="menu-card" onclick="location.href='gamble.php'">
                    <i class="fa-solid fa-dice-d20"></i>
                    <span>도박장</span><span class="sub">운 시험하기</span></div>
                <div class="menu-card" onclick="location.href='log.php'">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>기록</span><span class="sub">내 활동 내역</span></div>
            </div>
            <?php endif; ?>
        </div>

        <div id="battle-modal" class="modal-overlay">
            <div class="modal-content">
                <h3 style="margin-top:0;">⚔️ 싸움 방식 선택</h3>
                <button
                    class="btn-modal"
                    style="background:#2ecc71; color:white;"
                    onclick="App.startPvE()">
                    <b>🌲 학교 탐색</b><br>
                    <small>몬스터와 싸웁니다.</small>
                </button>
                <button
                    class="btn-modal"
                    style="background:#e74c3c; color:white;"
                    onclick="App.openUserList()">
                    <b>🤬 유저와 다툼</b><br>
                    <small>상대를 지목합니다.</small>
                </button>
                <button
                    onclick="App.closeModals()"
                    style="background:none; border:none; color:#999; cursor:pointer; margin-top:10px;">닫기</button>
            </div>
        </div>

        <div id="user-list-modal" class="modal-overlay">
            <div class="modal-content" style="max-height:80vh; overflow-y:auto;">
                <h3 style="margin-top:0;">시비 걸 상대 선택</h3>
                <div id="user-list-box">로딩 중...</div>
                <button
                    class="btn-main"
                    style="margin-top:15px; background:#999;"
                    onclick="App.closeModals()">닫기</button>
            </div>
        </div>
        <?php endif; ?>

        <script>
            const App = {
                challengeRoomId: 0,

                init: function () {
                    <?php if ($is_login): ?>
                    this.loadMyInfo();
                    setInterval(() => this.loadMyInfo(), 5000); // 5초마다 갱신
                    <?php endif; ?>
            },

            api: async function (data) {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                return await response.json();
            },

            login: async function () {
                const name = document
                    .getElementById('login-name')
                    .value;
                const pw = document
                    .getElementById('login-pw')
                    .value;
                if (!name || !pw) 
                    return alert('이름과 비밀번호를 입력하세요.');
                const res = await this.api({cmd: 'login', name: name, pw: pw});
                if (res.status === 'success') 
                    location.reload();
                else 
                    alert(res.message);
                }
            ,

            loadMyInfo: async function () {
                try {
                    const res = await this.api({cmd: 'get_my_info'});
                    if (res.status === 'success') {
                        const me = res.data;
                        document
                            .getElementById('ui-name')
                            .textContent = me.name;

                        if (me.role === 'admin') {
                            document
                                .getElementById('ui-stat')
                                .textContent = "관리자 권한 접속 중";
                        } else {
                            document
                                .getElementById('ui-stat')
                                .textContent = `Lv.${me
                                .level} | ${parseInt(me.point)
                                .toLocaleString()} P`;
                            // 부상 표시
                            const inj = parseInt(me.injury || 0);
                            const injNames = ["정상", "경상", "중상", "위독", "사망"];
                            document
                                .getElementById('ui-injury')
                                .innerHTML = `<span class="injury-badge inj-${inj}">상태: ${injNames[inj]} (${inj}/4)</span>`;
                            if (inj >= 4) {
                                document
                                    .querySelector('.dashboard-grid')
                                    .style
                                    .opacity = '0.5';
                                document
                                    .querySelector('.dashboard-grid')
                                    .style
                                    .pointerEvents = 'none';
                            }
                        }

                        const avatarBox = document.querySelector('.profile-avatar');
                        if (me.img_profile) {
                            // 이미지가 있으면 img 태그로 교체
                            avatarBox.innerHTML = `<img src="${me.img_profile}" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">`;
                        } else {
                            // 없으면 기본 아이콘
                            avatarBox.innerHTML = `<i class="fa-solid fa-user"></i>`;
                        }

                        // 결투 신청 확인
                        if (me.challenge) {
                            document
                                .getElementById('challenge-alert')
                                .style
                                .display = 'block';
                            document
                                .getElementById('chal-sender')
                                .textContent = me.challenge.name;
                            this.challengeRoomId = me.challenge.room_id;
                        } else {
                            document
                                .getElementById('challenge-alert')
                                .style
                                .display = 'none';
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            /* 싸움 관련 로직 */
            openBattleModal: function () {
                document
                    .getElementById('battle-modal')
                    .style
                    .display = 'flex';
            },
            closeModals: function () {
                document
                    .getElementById('battle-modal')
                    .style
                    .display = 'none';
                document
                    .getElementById('user-list-modal')
                    .style
                    .display = 'none';
            },

            startPvE: async function () {
                const res = await this.api({cmd: 'battle_make_room'});
                if (res.status === 'success') 
                    location.href = 'battle.php';
                else 
                    alert(res.message);
                }
            ,

            openUserList: async function () {
                document
                    .getElementById('battle-modal')
                    .style
                    .display = 'none';
                document
                    .getElementById('user-list-modal')
                    .style
                    .display = 'flex';
                const box = document.getElementById('user-list-box');
                box.innerHTML = '로딩 중...';

                const res = await this.api({cmd: 'battle_list_users'});
                if (res.status === 'success') {
                    let html = '';
                    res
                        .list
                        .forEach(u => {
                            html += `
                <div class="user-item">
                    <div style="text-align:left;"><b>${u.name}</b> (Lv.${u.level})<br><small>부상: ${u.injury}/4</small></div>
                    <button onclick="App.challengeUser(${u.id}, '${u.name}')" style="background:#e74c3c; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">도전</button>
                </div>`;
                        });
                    box.innerHTML = html || '도전 가능한 유저가 없습니다.';
                }
            },

            challengeUser: async function (tid, name) {
                if (!confirm(name + "님에게 싸움을 거시겠습니까?")) 
                    return;
                const res = await this.api({cmd: 'battle_challenge', target_id: tid});
                if (res.status === 'success') {
                    alert(res.msg);
                    location.href = 'battle.php';
                } else 
                    alert(res.message);
                }
            ,

            acceptChallenge: async function () {
                const res = await this.api({cmd: 'battle_join', room_id: this.challengeRoomId});
                if (res.status === 'success') 
                    location.href = 'battle.php';
                else 
                    alert(res.message);
                }
            };

        window.onload = () => App.init();
        </script>
    </body>
</html>