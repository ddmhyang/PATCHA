<style>
    .content {
        width: 1440px;
        height: 810px;
        flex-shrink: 0;
        background-size: cover;
        background-color: #ffffff;
        transform-origin: top left;
        position: absolute;
        transition: background-color 1s ease-in-out;
        font-family: "Tinos", "Noto Sans KR";
    }
            
    a{
        white-space: nowrap;
        text-decoration: none;
    }
    .secret-form-wrapper {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
        text-align: center;
        color: #fff;
    }

    .secret_icon {
        width: 60px;
        height: 60px;
        background: url('/assets/images/100-icon-password-w.png') center center / contain no-repeat;
        margin: 0 auto 20px;
    }

    .secret-form-wrapper h3 {
        font-size: 20px;
        font-weight: normal;
        margin-bottom: 30px;
    }

    .password-input-group {
        position: relative;
    }

    #secret_password {
        width: 100%;
        background: transparent;
        border: none;
        border-bottom: 2px solid #fff;
        padding: 10px 0;
        color: #fff;
        font-size: 16px;
        text-align: center;
    }

    #secret_password:focus {
        outline: none;
        border-bottom-color: #a9c4ff;
    }
    
    #secret_password::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .secret-submit-btn {
        width: 100%;
        padding: 12px;
        margin-top: 30px;
        background-color: rgba(255, 255, 255, 0.2);
        border: 1px solid #fff;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    .secret-submit-btn:hover {
        background-color: rgba(255, 255, 255, 0.4);
    }
</style>

<div class="content">
    <div class="secret-form-wrapper">
        <div class="secret_icon"></div>
        <h3>SECRET POST</h3>
        <form id="secretForm">
            <div class="password-input-group">
                <input type="password" id="secret_password" placeholder="비밀번호를 입력하세요" required>
            </div>
            <button type="submit" class="secret-submit-btn">확인</button>
        </form>
    </div>
</div>

<script>
function adjustScale() {
    const content = document.querySelector('.content');
    if (!content) 
        return;
    
    let contentWidth,
        contentHeight;
    const windowWidth = window.innerWidth;
    const windowHeight = window.innerHeight;

    if (windowWidth <= 768) {
        contentWidth = 720;
        contentHeight = 1280;
    } else {
        contentWidth = 1440;
        contentHeight = 810;
    }

    const scale = Math.min(
        windowWidth / contentWidth,
        windowHeight / contentHeight
    );
    content.style.transform = `scale(${scale})`;
    content.style.left = `${ (windowWidth - contentWidth * scale) / 2}px`;
    content.style.top = `${ (windowHeight - contentHeight * scale) / 2}px`;

}

window.addEventListener('load', () => {
    adjustScale();
    document.body.style.visibility = 'visible';
});

window.addEventListener('resize', adjustScale);

$(document).ready(function() {
    $('#secretForm').on('submit', function(e) {
        e.preventDefault();
        
        const params = new URLSearchParams(window.location.hash.split('?')[1]);
        const board = params.get('board');
        const id = params.get('id');
        const password = $('#secret_password').val();

        $.ajax({
            url: '/actions/verify_password.php',
            type: 'POST',
            data: {
                board: board,
                id: id,
                password: password
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.hash = '/list_page_' + board + '.php?id=' + id;
                } else {
                    alert('암호가 틀렸습니다.');
                }
            },
            error: function() {
                alert('인증 처리 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>