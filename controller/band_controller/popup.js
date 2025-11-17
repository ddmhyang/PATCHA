/*
 * popup.js
 * 크롬 확장 프로그램 팝업의 모든 로직(두뇌)을 담당합니다.
 * (★'아이템 양도' 탭 및 동적 인벤토리 로드 기능 추가됨)
 */

// -----------------------------------------------------------------
// ★★★★★★★ (가장 중요) ★★★★★★★
// 이 URL을 사용자님의 닷홈(Dothome) 주소로 정확히 바꿔주세요!
// -----------------------------------------------------------------
const API_BASE_URL = 'https://z3rdk9.dothome.co.kr/'; // (이 주소는 이미 정확합니다)

// --- 1. 전역 변수: 자주 쓰는 HTML 요소들 ---
const resultBox = document.getElementById('result-box');
const errorMessage = document.getElementById('error-message');

// --- 2. 초기화: 팝업이 열릴 때마다 실행 (DOMContentLoaded) ---
document.addEventListener('DOMContentLoaded', () => {
    // 2-1. 탭 버튼에 클릭 이벤트 연결
    setupTabs();
    
    // 2-2. 6개 폼에 'submit' 이벤트 연결
    document.getElementById('point-form').addEventListener('submit', handlePointForm);
    document.getElementById('transfer-point-form').addEventListener('submit', handleTransferPointForm); // ★ ID 변경
    document.getElementById('transfer-item-form').addEventListener('submit', handleTransferItemForm); // ★★★ 신규
    document.getElementById('gamble-form').addEventListener('submit', handleGambleForm);
    document.getElementById('item-form').addEventListener('submit', handleItemForm);
    document.getElementById('info-form').addEventListener('submit', handleInfoForm);

    // 2-3. (★핵심★) 팝업이 열릴 때마다, 모든 드롭다운(select) 목록을
    // 서버에서 새로고침합니다.
    preloadAllDropdownData();
    
    // 2-4. (★★★ 신규 ★★★) '아이템 양도' 탭의 동적 이벤트 리스너 연결
    const senderSelect = document.getElementById('sender-id-item-transfer');
    if (senderSelect) {
        senderSelect.addEventListener('change', handleSenderChangePopup);
    }
    const itemSelect = document.getElementById('item-id-transfer');
    if (itemSelect) {
        itemSelect.addEventListener('change', handleItemChangePopup);
    }
});

// --- 3. 탭 전환 로직 ---
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.dataset.tab;

            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
}

// --- 4. 드롭다운 목록 미리 불러오기 ---
async function preloadAllDropdownData() {
    clearMessages();
    try {
        const [membersRes, itemsRes, gamesRes] = await Promise.all([
            fetch(`${API_BASE_URL}api_get_all_members.php`),
            fetch(`${API_BASE_URL}api_get_all_items.php`),
            fetch(`${API_BASE_URL}api_get_all_games.php`)
        ]);

        const membersResult = await membersRes.json();
        const itemsResult = await itemsRes.json();
        const gamesResult = await gamesRes.json();

        if (membersResult.status !== 'success' || itemsResult.status !== 'success' || gamesResult.status !== 'success') {
            throw new Error('데이터 로드 실패. 닷홈 관리자 페이지에 로그인했는지 확인하세요.');
        }

        // 1. 회원 목록 채우기 (모든 탭)
        const allMemberSelects = document.querySelectorAll(
            'select[name="member_id"], select[name="sender_id"], select[name="receiver_id"]'
        );
        allMemberSelects.forEach(selectBox => {
            populateSelect(selectBox, membersResult.data, 'member_id', 'member_name');
        });

        // 2. 아이템 목록 채우기 ('아이템 지급' 탭)
        const itemSelect = document.getElementById('item-id-select');
        populateSelect(itemSelect, itemsResult.data, 'item_id', 'item_name');

        // 3. 도박 목록 채우기 ('도박' 탭)
        const gameSelect = document.getElementById('game-id-select');
        populateSelect(gameSelect, gamesResult.data, 'game_id', 'game_name');

    } catch (error) {
        showError(`초기화 실패: ${error.message}`);
    }
}

/**
 * (헬퍼 함수) <select> 드롭다운을 데이터로 채워줍니다.
 * @param {HTMLSelectElement} selectElement - 채울 <select> 요소
 * @param {Array} data - API에서 받은 데이터 배열
 * @param {string} valueField - <option>의 value가 될 키
 * @param {string} textField - <option>의 텍스트가 될 키
 * @param {string} [optionalField] - (선택) 괄호 안에 추가로 표시할 키
 */
function populateSelect(selectElement, data, valueField, textField, optionalField = null) {
    if (!data || data.length === 0) {
        selectElement.innerHTML = `<option value="">-- 데이터 없음 --</option>`;
        selectElement.disabled = true;
        return;
    }
    
    const optionsHtml = data.map(item => {
        let text = item[textField];
        // (★ 아이템 양도 탭의 동적 로드를 위한 코드)
        if (optionalField && item[optionalField]) {
            text += ` (보유: ${item[optionalField]})`;
        }
        // (★ data-quantity 속성 추가)
        return `<option value="${item[valueField]}" data-quantity="${item[optionalField] || 0}">${text}</option>`;
    });
    
    selectElement.innerHTML = `<option value="">-- 선택 --</option>` + optionsHtml.join('');
    selectElement.disabled = false;
}


// --- 5. 폼 전송 핸들러 ---

// (A) 포인트 폼
async function handlePointForm(event) {
    event.preventDefault();
    const form = event.target;
    const formData = {
        member_id: form.member_id.value,
        points: parseInt(form.points.value),
        reason: form.reason.value
    };
    
    const result = await callApi('admin_give_point.php', formData);
    if (result) {
        showResult(result.message);
        form.reset();
    }
}

// (B) 포인트 양도 폼 (★ ID 변경)
async function handleTransferPointForm(event) {
    event.preventDefault();
    const form = event.target;
    const formData = {
        sender_id: form.sender_id.value,
        receiver_id: form.receiver_id.value,
        amount: parseInt(form.amount.value)
    };

    const result = await callApi('api_transfer_points.php', formData);
    if (result) {
        showResult(result.message);
    }
}

// (C) ★★★ 신규: 아이템 양도 폼 ★★★
async function handleTransferItemForm(event) {
    event.preventDefault();
    const form = event.target;
    const formData = {
        sender_id: form.sender_id.value,
        receiver_id: form.receiver_id.value,
        item_id: parseInt(form.item_id.value),
        quantity: parseInt(form.quantity.value)
    };

    const result = await callApi('api_transfer_item.php', formData);
    if (result) {
        showResult(result.message);
        // (성공 시 폼 리셋)
        form.reset();
        document.getElementById('item-id-transfer').innerHTML = '<option value="">먼저 \'보내는 분\'을 선택하세요</option>';
        document.getElementById('item-id-transfer').disabled = true;
        document.getElementById('quantity-transfer').disabled = true;
        document.getElementById('transfer-item-submit').disabled = true;
    }
}

// (D) 도박 폼
async function handleGambleForm(event) {
    event.preventDefault();
    const form = event.target;
    const formData = {
        member_id: form.member_id.value,
        game_id: parseInt(form.game_id.value),
        bet_amount: parseInt(form.bet_amount.value)
    };

    const result = await callApi('run_gamble.php', formData);
    if (result) {
        showResult(result.message);
    }
}

// (E) 아이템 지급 폼 (구매/지급)
async function handleItemForm(event) {
    event.preventDefault();
    const form = event.target;
    const isPurchase = document.getElementById('item-is-purchase').checked;
    const endpoint = isPurchase ? 'buy_item.php' : 'api_admin_give_item.php';

    const formData = {
        member_id: form.member_id.value,
        item_id: parseInt(form.item_id.value),
        quantity: parseInt(form.quantity.value)
    };

    const result = await callApi(endpoint, formData);
    if (result) {
        showResult(result.message);
        form.reset();
        document.getElementById('item-is-purchase').checked = false;
    }
}

// (F) 정보 조회 폼
async function handleInfoForm(event) {
    event.preventDefault();
    clearMessages();
    const form = event.target;
    const memberId = form.member_id.value;

    try {
        // (★ 유일한 GET 방식 API)
        const response = await fetch(`${API_BASE_URL}get_user_info.php?member_id=${memberId}`);
        if (!response.ok) {
            throw new Error('서버 응답이 올바르지 않습니다.');
        }

        const result = await response.json();
        if (result.status === 'success') {
            const info = result.data;
            let message = `💬 [${info.member_name} (${info.member_id})] 님 정보\n`;
            message += `====================\n`;
            message += `포인트: ${info.points.toLocaleString()} P\n`;
            message += `--- 인벤토리 ---\n`;
            
            if (info.inventory.length === 0) {
                message += `(아이템 없음)`;
            } else {
                info.inventory.forEach(item => {
                    message += `[${item.item_name}] x ${item.quantity}\n`;
                });
            }
            showResult(message);
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showError(error.message);
    }
}


// --- 6. API 호출 유틸리티 (POST 전용) ---
async function callApi(endpoint, body) {
    clearMessages();
    try {
        const response = await fetch(API_BASE_URL + endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            throw new Error(`서버 오류: ${response.statusText}`);
        }

        const result = await response.json();
        
        if (result.status === 'error') {
            throw new Error(result.message);
        }

        return result;

    } catch (error) {
        showError(error.message);
        return null;
    }
}

// --- 7. (★★★ 신규 ★★★) '아이템 양도' 탭 동적 로직 ---

// ('보내는 분'이 바뀔 때 실행)
async function handleSenderChangePopup(event) {
    const senderId = event.target.value;
    const itemSelect = document.getElementById('item-id-transfer');
    const quantityInput = document.getElementById('quantity-transfer');
    const submitButton = document.getElementById('transfer-item-submit');

    // 리셋
    itemSelect.innerHTML = '<option value="">불러오는 중...</option>';
    itemSelect.disabled = true;
    quantityInput.disabled = true;
    submitButton.disabled = true;

    if (!senderId) {
        itemSelect.innerHTML = '<option value="">먼저 \'보내는 분\'을 선택하세요</option>';
        return;
    }

    try {
        // (★ 우리가 SPA용으로 만든 GET API 호출)
        const response = await fetch(`${API_BASE_URL}api_get_member_inventory.php?member_id=${senderId}`);
        const result = await response.json();

        if (result.status === 'success') {
            // (헬퍼 함수를 이용해 '보유 수량'까지 표시)
            populateSelect(itemSelect, result.data, 'item_id', 'item_name', 'quantity');
        } else {
            populateSelect(itemSelect, [], '', ''); // 데이터 없음으로 리셋
        }
    } catch (error) {
        showError(error.message);
    }
}

// ('보유 아이템'이 바뀔 때 실행)
function handleItemChangePopup(event) {
    const itemSelect = event.target;
    const quantityInput = document.getElementById('quantity-transfer');
    const submitButton = document.getElementById('transfer-item-submit');

    // <option>에 저장해둔 data-quantity 값을 가져옴
    const selectedOption = itemSelect.options[itemSelect.selectedIndex];
    
    if (!selectedOption || !selectedOption.value) {
        quantityInput.value = 1;
        quantityInput.disabled = true;
        submitButton.disabled = true;
        return;
    }

    const maxQuantity = parseInt(selectedOption.dataset.quantity || 0);

    if (maxQuantity > 0) {
        quantityInput.max = maxQuantity; // (★핵심★) 수량 input의 최대값을 보유 수량으로 제한
        quantityInput.value = 1; // 1로 리셋
        quantityInput.disabled = false;
        submitButton.disabled = false;
    }
}


// --- 8. 메시지창 유틸리티 ---
function showResult(message) {
    resultBox.value = message;
    errorMessage.textContent = '';
}
function showError(message) {
    errorMessage.textContent = message;
    resultBox.value = '';
}
function clearMessages() {
    resultBox.value = '';
    errorMessage.textContent = '';
}