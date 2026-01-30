<?php
// index.php : 메인 화면 (반응형 풀스크린 리메이크)
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>School Survival</title>
    <link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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

        /* === 상단 헤더 (꽉 찬 화면) === */
        header {
            background: var(--primary);
            color: var(--white);
            padding: 15px 30px;
            font-weight: 800;
            font-size: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(206, 89, 97, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        header .brand { display: flex; align-items: center; gap: 10px; }
        header .logout { font-size: 14px; background: rgba(0,0,0,0.1); padding: 5px 12px; border-radius: 20px; cursor: pointer; transition: 0.2s; }
        header .logout:hover { background: rgba(0,0,0,0.2); }

        /* === 메인 컨테이너 === */
        .container {
            width: 100%;
            max-width: 1200px; /* 너무 퍼지지 않게 적당한 선에서 잡아줌 */
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
            flex: 1;
        }

        /* === 로그인 전용 스타일 (중앙 정렬) === */
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }
        .login-box {
            background: white;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-logo { font-size: 60px; color: var(--primary); margin-bottom: 20px; }
        .login-title { font-size: 26px; font-weight: 800; margin-bottom: 30px; color: var(--text); }
        
        input {
            width: 100%;
            padding: 16px;
            margin-bottom: 12px;
            border: 2px solid #eee;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Pretendard';
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--point); outline: none; }
        
        .btn-main {
            width: 100%;
            padding: 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.2s;
        }
        .btn-main:hover { background: #b0464d; transform: translateY(-2px); }

        /* === 대시보드 그리드 (반응형) === */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* 화면 크기에 따라 자동 배치 */
            gap: 20px;
            margin-top: 20px;
        }

        .menu-card {
            background: white;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1.2 / 1; /* 약간 가로로 넓은 직사각형 */
        }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: var(--point); }
        .menu-card i { font-size: 40px; margin-bottom: 15px; color: var(--primary); transition: 0.2s; }
        .menu-card:hover i { transform: scale(1.1); }
        .menu-card span { font-weight: 700; font-size: 18px; color: #555; }
        .menu-card .sub { font-size: 13px; color: #999; margin-top: 5px; font-weight: 400; }

        /* 프로필 카드 (가로로 긴 형태) */
        .profile-card {
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(206, 89, 97, 0.3);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .profile-info h1 { margin: 0; font-size: 28px; font-weight: 800; }
        .profile-info p { margin: 5px 0 0; opacity: 0.9; font-size: 16px; }
        .profile-avatar {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px;
        }

        /* 유틸 */
        .section-title { font-size: 18px; font-weight: 700; color: #777; margin-bottom: 10px; margin-left: 5px; }
    </style>
</head>
<body>

<?php if (!$is_login): ?>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo"><i class="fa-solid fa-school"></i></div>
            <div class="login-title">졸업하게 해주세요!</div>
            <input type="text" id="login-name" placeholder="이름">
            <input type="password" id="login-pw" placeholder="비밀번호">
            <button class="btn-main" onclick="App.login()">졸업식 마무리하기</button>
        </div>
    </div>
<?php else: ?>
    <header>
        <div class="brand"><i class="fa-solid fa-graduation-cap"></i> 졸업하게 해주세요!</div>
        <div class="logout" onclick="location.href='index.php?logout=1'"><i class="fa-solid fa-right-from-bracket"></i> 로그아웃</div>
    </header>

    <div class="container">
        <div class="profile-card">
            <div class="profile-info">
                <h1 id="my-name">불러오는 중...</h1>
                <p id="my-stat">데이터를 동기화하고 있습니다.</p>
            </div>
            <div class="profile-avatar"><i class="fa-solid fa-user"></i></div>
        </div>

        <?php if ($role === 'admin'): ?>
            <div class="section-title">관리자 제어 패널</div>
            <div class="dashboard-grid">
                <div class="menu-card" onclick="location.href='admin_member.php'">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>캐릭터 관리</span>
                    <span class="sub">생성, 수정, 삭제</span>
                </div>
                <div class="menu-card" onclick="location.href='admin_item.php'">
                    <i class="fa-solid fa-shirt"></i>
                    <span>아이템 설정</span>
                    <span class="sub">상점 및 장비 도감</span>
                </div>
                <div class="menu-card" onclick="location.href='admin_monster.php'">
                    <i class="fa-solid fa-skull-crossbones"></i>
                    <span>몬스터 설정</span>
                    <span class="sub">스텟 및 드랍</span>
                </div>
                <div class="menu-card" onclick="location.href='admin_status.php'">
                    <i class="fa-solid fa-flask"></i>
                    <span>상태이상</span>
                    <span class="sub">감염병 및 효과</span>
                </div>
                <div class="menu-card" onclick="location.href='admin_gamble.php'">
                    <i class="fa-solid fa-dice"></i>
                    <span>도박장</span>
                    <span class="sub">확률 조정</span>
                </div>

                <div class="menu-card" onclick="location.href='admin_battle.php'">
                    <i class="fa-solid fa-server"></i>
                    <span>전투 방 관리</span>
                    <span class="sub">오류 방 삭제</span>
                </div>

                <div class="menu-card" onclick="location.href='log.php'">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>활동 기록</span>
                    <span class="sub">최근 내역 확인</span>
                </div>
            </div>

        <?php else: ?>
            <div class="section-title">학교 생활</div>
            <div class="dashboard-grid">
                <div class="menu-card" onclick="location.href='inventory.php'">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>내 가방</span>
                    <span class="sub">아이템 및 장비</span>
                </div>
                <div class="menu-card" onclick="location.href='battle.php'">
                    <i class="fa-solid fa-hand-fist"></i>
                    <span>싸움(모험)</span>
                    <span class="sub">학교 탐색</span>
                </div>
                <div class="menu-card" onclick="location.href='shop.php'">
                    <i class="fa-solid fa-shop"></i>
                    <span>매점</span>
                    <span class="sub">물건 구매</span>
                </div>
                <div class="menu-card" onclick="location.href='gamble.php'">
                    <i class="fa-solid fa-dice-d20"></i>
                    <span>도박장</span>
                    <span class="sub">운 시험하기</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
const App = {
    init: function() {
        <?php if ($is_login): ?>
        this.loadMyInfo();
        <?php endif; ?>
    },

    login: async function() {
        const name = document.getElementById('login-name').value;
        const pw = document.getElementById('login-pw').value;
        if(!name || !pw) { alert('이름과 비밀번호를 입력하세요.'); return; }

        try {
            const res = await this.api({ cmd: 'login', name: name, pw: pw });
            if(res.status === 'success') { location.reload(); } 
            else { alert(res.message); }
        } catch(e) { alert('오류 발생'); }
    },

    loadMyInfo: async function() {
        try {
            const res = await this.api({ cmd: 'get_my_info' });
            if(res.status === 'success') {
                const me = res.data;
                document.getElementById('my-name').textContent = me.name;
                if(me.role === 'admin') {
                    document.getElementById('my-stat').textContent = "관리자 권한으로 접속 중";
                } else {
                    document.getElementById('my-stat').textContent = `Lv.${me.level} | ${me.point.toLocaleString()} P`;
                }
            }
        } catch(e) { console.error(e); }
    },

    api: async function(data) {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return await response.json();
    }
};
window.onload = () => App.init();
</script>
</body>
</html>