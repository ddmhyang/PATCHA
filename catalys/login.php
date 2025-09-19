<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>CATALYS - LOGIN</title>
        <style>
            /* index.php의 기본 스타일 */
            body,
            html {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                background-color: #0B2673;
                overflow: hidden;
                position: relative;
                visibility: hidden;
            }

            .container {
                width: 1440px;
                height: 810px;
                flex-shrink: 0;
                background-size: cover;
                background-color: transparent; 
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
            }

            a{
                white-space: nowrap;
                text-decoration: none;
            }
            
            a:visited {
                color: inherit;
            }

            .container,
            body,
            html {
                transition: background-color 1s ease-in-out;
                font-family: "Tinos";
            }
            
            /* 로그인 폼을 위한 스타일 */
            .login-form-container {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 400px;
                text-align: center;
                color: #0B2673;
                font-family: "Tinos";
                background-color: #ffffff;
                padding: 100px 150px;
            }
            
            .input-group {
                position: relative;
                margin-bottom: 25px;
            }
            
            .login-input {
                width: 100%;
                background: transparent;
                border: none;
                border-bottom: 1px solid #0B2673;
                padding: 10px 0;
                color: #0B2673;
                font-size: 16px;
            }

            .login-input:focus {
                outline: none;
                border-bottom-color: #0051ffff;
            }

            .login-input::placeholder {
                color: #0B267377;
                font-family: "Tinos";
            }

            .login-button {
                width: 100%;
                padding: 12px;
                background-color: rgba(255, 255, 255, 0.2);
                border: 1px solid #0B2673;
                color: #0B2673;
                font-size: 18px;
                cursor: pointer;
                border-radius: 5px;
                transition: background-color 0.3s;
                font-family: "Tinos";
            }

            .login-button:hover {
                background-color: #0B267304;
            }

            #admin_id, #admin_pass {
                font-family: "Tinos";
            }

        </style>
    </head>
    <body>
        <div class="container">
            
            <div class="login-form-container">
                <form id="loginForm">
                    <div class="input-group">
                        <input type="text" id="admin_id" name="admin_id" class="login-input" placeholder="ID" required>
                    </div>
                    <div class="input-group">
                        <input type="password" id="admin_pass" name="admin_pass" class="login-input" placeholder="PASSWORD" required>
                    </div>
                    <button type="submit" class="login-button">LOGIN</button>
                </form>
            </div>

        </div>
        <script>
            // index.php의 반응형 레이아웃 스크립트
            function adjustScale() {
                const container = document.querySelector('.container');
                if (!container) return;
                
                let containerWidth, containerHeight;
                const windowWidth = window.innerWidth;
                const windowHeight = window.innerHeight;

                if (windowWidth <= 768) {
                    containerWidth = 720;
                    containerHeight = 1280;
                } else {
                    containerWidth = 1440;
                    containerHeight = 810;
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

            // 기존 login.php의 로그인 처리 스크립트
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                fetch('actions/login_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('로그인 되었습니다.');
                        window.location.href = 'index.php'; // 로그인 성공 시 index.php로 이동
                    } else {
                        alert('아이디 또는 비밀번호가 일치하지 않습니다.');
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        </script>
    </body>
</html>