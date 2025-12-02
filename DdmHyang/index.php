<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DdmHyang</title>
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Noto+Sans+KR:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" sizes="128x128" href="assets/images/favicon.png">
    <style>
        :root {
            --bg-color: #fffafc;
            --accent-pink: #ffc0cb;
            --accent-dark: #554247;
            --frame-border: #f0e6e8;
            --font-serif: 'Abril Fatface', cursive;
            --font-sans: 'Noto Sans KR', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: rgb(0, 0, 0);
            overflow: hidden;
            position: relative;
            visibility: hidden;
            font-family: var(--font-sans);
        }

        body {
            background-color: var(--bg-color);
            color: var(--accent-dark);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: linear-gradient(90deg, var(--frame-border) 1px, transparent 1px), linear-gradient(var(--frame-border) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .container {
            width: 1440px;
            height: 900px;
            flex-shrink: 0;
            transform-origin: top left;
            transform: scale(0);
            position: absolute;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .battery-container {
            width: 600px;
            height: 240px;
            border: 10px solid var(--accent-pink);
            border-radius: 40px;
            padding: 20px; 
            position: relative;
            box-shadow: 20px 20px 0 rgba(255, 192, 203, 0.4);
            display: flex;
            align-items: center;
            background-color: #fff;
        }

        .battery-container::after {
            content: '';
            position: absolute;
            right: -40px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px; 
            height: 80px; 
            background-color: var(--accent-pink);
            border-radius: 0 20px 20px 0;
        }

        .battery-level {
            height: 100%;
            width: 0%;
            background: repeating-linear-gradient(
                45deg,
                var(--accent-pink),
                var(--accent-pink) 20px,
                #ffdee3 20px,
                #ffdee3 40px
            );
            border-radius: 20px;
            transition: width 0.2s ease;
            position: relative;
        }

        .charge-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 6rem;
            color: var(--accent-dark);
            opacity: 0;
            z-index: 10;
            text-shadow: 4px 4px 0 #fff;
        }

        .loading-text {
            margin-top: 60px;
            font-family: var(--font-serif);
            font-size: 3rem;
            letter-spacing: 4px;
            color: var(--accent-dark);
        }

        .start-btn {
            display: none;
            margin-top: 60px;
            padding: 30px 80px;
            font-family: var(--font-serif);
            font-size: 3rem;
            color: #fff;
            background-color: var(--accent-pink);
            border: none;
            border-radius: 100px;
            cursor: pointer;
            box-shadow: 10px 10px 0 rgba(85, 66, 71, 0.2); 
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }

        .start-btn:hover {
            transform: translate(-6px, -6px); 
            box-shadow: 16px 16px 0 rgba(85, 66, 71, 0.3);
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .blinking {
            animation: blink 1s infinite;
        }


        @media screen and (max-width: 784px) {
            .container {
                width: 720px;
                height: 1280px;
                flex-shrink: 0;
                transform-origin: top left;
                transform: scale(0);
                position: absolute;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="battery-container">
            <div class="battery-level" id="level"></div>
            <i class="fa-solid fa-bolt charge-icon" id="bolt"></i>
        </div>

        <div class="loading-text" id="text">Charging... 0%</div>

        <a href="pages/main.php" class="start-btn" id="startBtn">
            SYSTEM START <i class="fa-solid fa-power-off"></i>
        </a>
    </div>

    <script>
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
                containerHeight = 900;
            }

            const scale = Math.min(
                windowWidth / containerWidth,
                windowHeight / containerHeight
            );
            container.style.transform = `scale(${scale})`;
            container.style.left = `${(windowWidth - containerWidth * scale) / 2}px`;
            container.style.top = `${(windowHeight - containerHeight * scale) / 2}px`;
        }

        window.addEventListener('load', () => {
            adjustScale();
            document.body.style.visibility = 'visible';
        });
        window.addEventListener('resize', adjustScale);

        const levelBar = document.getElementById('level');
        const textLabel = document.getElementById('text');
        const boltIcon = document.getElementById('bolt');
        const startBtn = document.getElementById('startBtn');

        let percentage = 0;

        const loadingInterval = setInterval(() => {
            percentage += 1;

            levelBar.style.width = percentage + '%';
            textLabel.innerText = `Charging... ${percentage}%`;

            if (percentage > 50) {
                boltIcon.style.opacity = 1;
            }

            if (percentage >= 100) {
                clearInterval(loadingInterval); 
                finishLoading();
            }
        }, 15); 

        function finishLoading() {
            textLabel.innerText = "System Ready!";
            textLabel.classList.add('blinking');
            startBtn.style.display = 'inline-block';
        }
    </script>
</body>
</html>