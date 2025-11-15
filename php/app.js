/*
 * app.js
 * SPA의 모든 로직을 담당합니다.
 */

// API 파일들이 있는 기본 경로
const API_BASE_URL = '.'; // index.php와 같은 폴더

// 앱의 '메인 콘텐츠' 영역을 변수로 저장
const contentElement = document.getElementById('app-content');

// --- 페이지 로드 시 초기 설정 ---
document.addEventListener('DOMContentLoaded', () => {
    
    const navLinks = document.querySelectorAll('#main-nav a[data-page]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            event.preventDefault(); // 링크의 기본 이동(깜빡임)을 막음
            
            const pageName = event.target.dataset.page; // data-page="members"
            
            // 클릭된 링크에 'active' 클래스 부여
            navLinks.forEach(nav => nav.classList.remove('active'));
            event.target.classList.add('active');
            
            // 페이지 이름에 따라 다른 함수를 호출
            navigateTo(pageName);
        });
    });

    // 시작 페이지 로드 (예: 회원 관리)
    navigateTo('members');
    document.querySelector('#main-nav a[data-page="members"]').classList.add('active');
});

// --- 페이지 라우터 ---
function navigateTo(page) {
    console.log('페이지 로드:', page);
    switch (page) {
        case 'members':
            loadMembersPage();
            break;
        case 'items':
            loadItemsPage(); // (신규)
            break;
        case 'games':
            loadGamesPage(); // (신규)
            break;
        case 'logs':
            loadLogsPage(); // (신규)
            break;
        default:
            contentElement.innerHTML = '<h2>페이지를 찾을 수 없습니다.</h2>';
    }
}

// -----------------------------------------------------
// --- 1. 회원 관리 (Members) ---
// -----------------------------------------------------
async function loadMembersPage() {
    // 1. 폼과 테이블 뼈대 그리기
    const pageHtml = `
        <h2>회원 관리</h2>
        <form id="add-member-form">
            <h3>새 회원 등록</h3>
            <div class="form-group">
                <label for="member_id">회원 ID (밴드 닉네임)</label>
                <input type="text" id="member_id" name="member_id" required>
            </div>
            <div class="form-group">
                <label for="member_name">이름 (표시용)</label>
                <input type="text" id="member_name" name="member_name" required>
            </div>
            <button type="submit">등록하기</button>
            <p id="form-message"></p>
        </form>
        <h3>전체 회원 목록</h3>
        <table id="members-table">
            <thead><tr><th>회원 ID</th><th>이름</th><th>보유 포인트</th></tr></thead>
            <tbody><tr><td colspan="3">데이터 로딩 중...</td></tr></tbody>
        </table>
    `;
    contentElement.innerHTML = pageHtml;

    // 2. 폼 이벤트 리스너 추가
    document.getElementById('add-member-form').addEventListener('submit', handleAddMember);

    // 3. API 호출하여 테이블 채우기
    try {
        const response = await fetch(`${API_BASE_URL}/api_get_all_members.php`);
        const result = await response.json();
        const tableBody = document.querySelector('#members-table tbody');

        if (result.status === 'success' && result.data.length > 0) {
            const rowsHtml = result.data.map(member => `
                <tr>
                    <td>${member.member_id}</td>
                    <td>${member.member_name}</td>
                    <td>${member.points.toLocaleString()} P</td>
                </tr>
            `).join('');
            tableBody.innerHTML = rowsHtml;
        } else if (result.status === 'success') {
            tableBody.innerHTML = '<tr><td colspan="3">등록된 회원이 없습니다.</td></tr>';
        } else {
            tableBody.innerHTML = `<tr><td colspan="3" class="error">${result.message}</td></tr>`;
        }
    } catch (error) {
        document.querySelector('#members-table tbody').innerHTML = 
            `<tr><td colspan="3" class="error">데이터 로드 오류: ${error}</td></tr>`;
    }
}

async function handleAddMember(event) {
    event.preventDefault();
    const form = event.target;
    const messageElement = document.getElementById('form-message');
    const formData = {
        member_id: form.member_id.value,
        member_name: form.member_name.value
    };

    try {
        const response = await fetch(`${API_BASE_URL}/api_add_member.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const result = await response.json();

        if (result.status === 'success') {
            messageElement.textContent = result.message;
            messageElement.className = 'success';
            form.reset();
            loadMembersPage(); // 목록 새로고침
        } else {
            messageElement.textContent = result.message;
            messageElement.className = 'error';
        }
    } catch (error) {
        messageElement.textContent = `전송 오류: ${error}`;
        messageElement.className = 'error';
    }
}

// -----------------------------------------------------
// --- 2. 상점 관리 (Items) ---
// -----------------------------------------------------
async function loadItemsPage() {
    // 1. 폼과 테이블 뼈대 그리기
    const pageHtml = `
        <h2>상점 관리</h2>
        <form id="add-item-form">
            <h3>새 아이템 등록</h3>
            <div class="form-group">
                <label for="item_name">아이템 이름</label>
                <input type="text" id="item_name" name="item_name" required>
            </div>
            <div class="form-group">
                <label for="item_description">아이템 설명</label>
                <textarea id="item_description" name="item_description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="price">가격</label>
                <input type="number" id="price" name="price" value="0" min="0" required>
            </div>
            <div class="form-group">
                <label for="stock">재고 (-1은 무한)</label>
                <input type="number" id="stock" name="stock" value="-1" min="-1" required>
            </div>
            <div class="form-group">
                <label for="status">판매 상태</label>
                <select id="status" name="status">
                    <option value="selling">판매중</option>
                    <option value="sold_out">품절</option>
                </select>
            </div>
            <button type="submit">아이템 등록</button>
            <p id="form-message"></p>
        </form>
        <h3>상점 아이템 목록</h3>
        <table id="items-table">
            <thead><tr><th>ID</th><th>이름</th><th>설명</th><th>가격</th><th>재고</th><th>상태</th></tr></thead>
            <tbody><tr><td colspan="6">데이터 로딩 중...</td></tr></tbody>
        </table>
    `;
    contentElement.innerHTML = pageHtml;

    // 2. 폼 이벤트 리스너 추가
    document.getElementById('add-item-form').addEventListener('submit', handleAddItem);

    // 3. API 호출하여 테이블 채우기
    try {
        const response = await fetch(`${API_BASE_URL}/api_get_all_items.php`);
        const result = await response.json();
        const tableBody = document.querySelector('#items-table tbody');

        if (result.status === 'success' && result.data.length > 0) {
            const rowsHtml = result.data.map(item => `
                <tr>
                    <td>${item.item_id}</td>
                    <td>${item.item_name}</td>
                    <td>${item.item_description}</td>
                    <td>${item.price.toLocaleString()} P</td>
                    <td>${item.stock == -1 ? '무한' : item.stock.toLocaleString()}</td>
                    <td>${item.status === 'selling' ? '판매중' : '품절'}</td>
                </tr>
            `).join('');
            tableBody.innerHTML = rowsHtml;
        } else if (result.status === 'success') {
            tableBody.innerHTML = '<tr><td colspan="6">등록된 아이템이 없습니다.</td></tr>';
        } else {
            tableBody.innerHTML = `<tr><td colspan="6" class="error">${result.message}</td></tr>`;
        }
    } catch (error) {
        document.querySelector('#items-table tbody').innerHTML = 
            `<tr><td colspan="6" class="error">데이터 로드 오류: ${error}</td></tr>`;
    }
}

async function handleAddItem(event) {
    event.preventDefault();
    const form = event.target;
    const messageElement = document.getElementById('form-message');
    const formData = {
        item_name: form.item_name.value,
        item_description: form.item_description.value,
        price: parseInt(form.price.value),
        stock: parseInt(form.stock.value),
        status: form.status.value
    };

    try {
        const response = await fetch(`${API_BASE_URL}/api_add_item.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const result = await response.json();

        if (result.status === 'success') {
            messageElement.textContent = result.message;
            messageElement.className = 'success';
            form.reset();
            loadItemsPage(); // 목록 새로고침
        } else {
            messageElement.textContent = result.message;
            messageElement.className = 'error';
        }
    } catch (error) {
        messageElement.textContent = `전송 오류: ${error}`;
        messageElement.className = 'error';
    }
}

// -----------------------------------------------------
// --- 3. 도박 관리 (Games) ---
// -----------------------------------------------------
async function loadGamesPage() {
    // 1. 폼과 테이블 뼈대 그리기
    const pageHtml = `
        <h2>도박 관리</h2>
        <form id="add-game-form">
            <h3>새 도박 게임 등록</h3>
            <div class="form-group">
                <label for="game_name">게임 이름</label>
                <input type="text" id="game_name" name="game_name" required>
            </div>
            <div class="form-group">
                <label for="description">게임 설명</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="outcomes">배율 목록 (쉼표로 구분)</label>
                <input type="text" id="outcomes" name="outcomes" placeholder="-10,-5,0,1,5,10" required>
            </div>
            <button type="submit">게임 등록</button>
            <p id="form-message"></p>
        </form>
        <h3>도박 게임 목록</h3>
        <table id="games-table">
            <thead><tr><th>ID</th><th>게임 이름</th><th>설명</th><th>배율 목록</th></tr></thead>
            <tbody><tr><td colspan="4">데이터 로딩 중...</td></tr></tbody>
        </table>
    `;
    contentElement.innerHTML = pageHtml;

    // 2. 폼 이벤트 리스너 추가
    document.getElementById('add-game-form').addEventListener('submit', handleAddGame);

    // 3. API 호출하여 테이블 채우기
    try {
        const response = await fetch(`${API_BASE_URL}/api_get_all_games.php`);
        const result = await response.json();
        const tableBody = document.querySelector('#games-table tbody');

        if (result.status === 'success' && result.data.length > 0) {
            const rowsHtml = result.data.map(game => `
                <tr>
                    <td>${game.game_id}</td>
                    <td>${game.game_name}</td>
                    <td>${game.description}</td>
                    <td>${game.outcomes}</td>
                </tr>
            `).join('');
            tableBody.innerHTML = rowsHtml;
        } else if (result.status === 'success') {
            tableBody.innerHTML = '<tr><td colspan="4">등록된 도박 게임이 없습니다.</td></tr>';
        } else {
            tableBody.innerHTML = `<tr><td colspan="4" class="error">${result.message}</td></tr>`;
        }
    } catch (error) {
        document.querySelector('#games-table tbody').innerHTML = 
            `<tr><td colspan="4" class="error">데이터 로드 오류: ${error}</td></tr>`;
    }
}

async function handleAddGame(event) {
    event.preventDefault();
    const form = event.target;
    const messageElement = document.getElementById('form-message');
    const formData = {
        game_name: form.game_name.value,
        description: form.description.value,
        outcomes: form.outcomes.value
    };

    try {
        const response = await fetch(`${API_BASE_URL}/api_add_game.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(formData)
        });
        const result = await response.json();

        if (result.status === 'success') {
            messageElement.textContent = result.message;
            messageElement.className = 'success';
            form.reset();
            loadGamesPage(); // 목록 새로고침
        } else {
            messageElement.textContent = result.message;
            messageElement.className = 'error';
        }
    } catch (error) {
        messageElement.textContent = `전송 오류: ${error}`;
        messageElement.className = 'error';
    }
}

// -----------------------------------------------------
// --- 4. 포인트 로그 (Logs) ---
// -----------------------------------------------------
async function loadLogsPage() {
    // 1. (읽기 전용이므로 폼 없음) 테이블 뼈대 그리기
    const pageHtml = `
        <h2>포인트 로그</h2>
        <h3>전체 포인트 변동 내역</h3>
        <table id="logs-table">
            <thead>
                <tr>
                    <th>시간</th>
                    <th>회원 ID</th>
                    <th>회원 이름</th>
                    <th>변동 포인트</th>
                    <th>사유</th>
                </tr>
            </thead>
            <tbody><tr><td colspan="5">데이터 로딩 중...</td></tr></tbody>
        </table>
    `;
    contentElement.innerHTML = pageHtml;

    // 2. API 호출하여 테이블 채우기
    try {
        const response = await fetch(`${API_BASE_URL}/api_get_all_logs.php`);
        const result = await response.json();
        const tableBody = document.querySelector('#logs-table tbody');

        if (result.status === 'success' && result.data.length > 0) {
            const rowsHtml = result.data.map(log => {
                // 포인트 값에 따라 CSS 클래스와 텍스트(+,-) 설정
                let pointClass = '';
                let pointDisplay = log.point_change;
                if (log.point_change > 0) {
                    pointClass = 'success'; // .success { color: green }
                    pointDisplay = `+${log.point_change.toLocaleString()}`;
                } else if (log.point_change < 0) {
                    pointClass = 'error'; // .error { color: red }
                    pointDisplay = `${log.point_change.toLocaleString()}`;
                }
                
                return `
                    <tr>
                        <td>${log.log_time}</td>
                        <td>${log.member_id || 'N/A'}</td>
                        <td>${log.member_name || '알 수 없음'}</td>
                        <td class="${pointClass}">${pointDisplay} P</td>
                        <td>${log.reason}</td>
                    </tr>
                `;
            }).join('');
            tableBody.innerHTML = rowsHtml;
        } else if (result.status === 'success') {
            tableBody.innerHTML = '<tr><td colspan="5">포인트 변동 내역이 없습니다.</td></tr>';
        } else {
            tableBody.innerHTML = `<tr><td colspan="5" class="error">${result.message}</td></tr>`;
        }
    } catch (error) {
        document.querySelector('#logs-table tbody').innerHTML = 
            `<tr><td colspan="5" class="error">데이터 로드 오류: ${error}</td></tr>`;
    }
}