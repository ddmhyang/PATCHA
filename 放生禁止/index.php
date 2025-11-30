<?php require_once 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="ko">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>放生禁止</title>
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * {
                box-sizing: border-box;
            }

            @font-face {
                font-family: 'Fre1';
                src: url("assets/fonts/Freesentation-1Thin.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }
            @font-face {
                font-family: 'Fre3';
                src: url("assets/fonts/Freesentation-3Light.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }
            @font-face {
                font-family: 'Fre5';
                src: url("assets/fonts/Freesentation-5Medium.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }
            @font-face {
                font-family: 'Fre9';
                src: url("assets/fonts/Freesentation-9Black.ttf") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            body,
            html {
                margin: 0;
                padding: 0;
                width: 100vw;
                height: 100vh;
                background-color: rgb(255, 255, 255);
                overflow: hidden;
                position: relative;
                visibility: hidden;
                transition: background-color 1s ease-in-out;
            }
            .container {
                width: 1440px;
                height: 900px;
                flex-shrink: 0;
                aspect-ratio: 3/2;
                background-size: cover;
                background-color: #D4D4D4;
                transform-origin: top left;
                position: absolute;
                transform: scale(0);
                font-family: 'Fre3';
                color: #595959;
                transition: background-color 1s ease-in-out;
            }

            header {
                position: absolute;
                width: 1380px;
                height: 100px;
                left: 50%;
                top: 30px;
                transform: translateX(-50%);
                background-color: #595959;
                border-radius: 20px;
            }

            .index-title {
                position: relative;
                left: 36px;
                top: 20px;
                color: #d4d4d4;
                font-family: 'Fre5';
                font-size: 50px;
                font-style: normal;
                line-height: normal;
                text-decoration: none;
            }

            .side-profile {
                position: absolute;
                width: 280px;
                height: 300px;
                top: 160px;
                left: 30px;
                background-color: #595959;
                border-radius: 20px;

            }

            .sp-img {
                position: absolute;
                left: 30px;
                top: 30px;
                width: 220px;
                height: 160px;
                background: url("assets/images/profile.png") no-repeat center center;
                background-color: #D4D4D4;
                border-radius: 10px;
                background-size: cover;
            }

            .sP-dDay {
                position: absolute;
                top: 203px;
                left: 50%;
                transform: translateX(-50%);
                color: #D4D4D4;
                font-family: 'Fre9';
                font-size: 48px;
                font-style: normal;
                line-height: normal;
            }

            .sP-date {
                position: absolute;
                top: 262px;
                left: 50%;
                transform: translateX(-50%);
                color: #D4D4D4;
                font-family: 'Fre9';
                font-size: 20px;
                font-style: normal;
                line-height: normal;
            }

            .side-login {
                position: absolute;
                top: 590px;
                left: 30px;
                width: 280px;
                height: 280px;
                background-color: #59595900;
                border: #595959 solid 4px;
                border-radius: 20px;
            }

            .side-search {
                position: absolute;
                width: 280px;
                height: 70px;
                top: 490px;
                left: 30px;
                background-color: #59595900;
                border: #595959 solid 4px;
                border-radius: 20px;
            }

            main {
                position: absolute;
                width: 1060px;
                height: 710px;
                top: 160px;
                left: 340px;
                background-color: #59595900;
                border: #595959 solid 4px;
                border-radius: 20px;
                overflow-y: auto;
                padding: 40px 20px;
            }

            main::-webkit-scrollbar {
                width: 0;
                height: 0;
            }

            .login-form,
            .password_content {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
            }
            .login-form > form {
                display: flex;
                gap: 0;
                flex-direction: column;
                align-items: center;
            }

            .input-group {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-top: 20px;
            }

            .input-group input {
                color: #7078A7;
                width: 175px;
                height: 44px;
                flex-shrink: 0;
                border: none;
                outline: none;
                background: rgba(255, 255, 255, 0);
                text-align: center;
            }

            .rounded-bar {
                width: 175px;
                height: 4px;
                background-color: #595959;
                border-radius: 2px;
            }

            .input-group > button {
                color: #595959;
                width: 142px;
                height: 40px;
                flex-shrink: 0;
                border-radius: 30px;
                border: 3px solid #595959;
                background: #D4D4D4;
                margin-top: 10px;
                cursor: pointer;
            }

            #logout-btn {
                width: 142px;
                height: 40px;
                flex-shrink: 0;
                border-radius: 30px;
                border: 3px solid #595959;
                background: #d4d4d4;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -100%);
            }

            #logout-btn > a {
                width: 100%;
                height: 100%;
                color: #595959;
                text-decoration: none;
                text-align: center;
                margin-top: 7px;
                justify-items: center;
            }

            .add-btn {
                background: url("assets/images/new.png") no-repeat center center;
                background-size: cover;
                width: 30px;
                height: 30px;
                position: absolute;
                left: 870px;
                top: 30px;
            }

            .sort-btn {
                background: url("assets/images/deco.png") no-repeat center center;
                background-size: cover;
                width: 30px;
                height: 30px;
                position: absolute;
                left: 930px;
                top: 30px;
                transition: transform 0.2s ease-in-out;
            }
            .add-btn:hover,
            .sort-btn:hover {
                transform: scale(1.05);
            }

            .btn-delete-item {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: rgba(255, 0, 0, 0.7);
                color: white;
                border: none;
                border-radius: 5px;
                padding: 5px 10px;
                cursor: pointer;
                font-size: 12px;
                z-index: 10;
                display: none;
            }

            .gallery-item:hover .btn-delete-item {
                display: block;
            }

            .form-page-container,
            .gallery-container,
            .page-container,
            .view-container {
                margin: 80px 40px;
                color: #7078A7;
                font-family: 'Fre3';
                font-size: 20px;
                font-style: normal;
                line-height: normal;
            }

            .gallery-container > h2 {
                font-family: 'Fre9';
            }

            .gallery-grid {
                margin-top: 50px;
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 70px;
            }

            .gallery-item {
                text-decoration: none;
                color: #333;
                border-radius: 15px;
                overflow: hidden;
            }

            .gallery-item h3 {
                padding: 10px;
                font-size: 16px;
                margin: 0;
                color: #7078A7;
            }

            .item-thumbnail {
                width: 100%;
                padding-top: 100%;
                background-color: #B1C8DA;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
            }

            .view-container hr {
                border: none;
                border-top: 3px solid #7078A7;
            }

            .post-meta {
                text-align: right;
                margin-bottom: 15px;
            }

            .post-date {
                text-align: right;
                color: #7078A7;
                font-size: 14px;
                margin-bottom: 25px;
            }

            .post-content {
                line-height: 1.8;
                min-height: 250px;
            }

            .post-content img {
                max-width: 100%;
                height: auto;
                border-radius: 5px;
            }

            .post-actions {
                text-align: center;
                margin-top: 40px;
            }

            .btn-back-to-list {
                display: inline-block;
                padding: 12px 20px;
                background-color: #595959;
                color: white;
                border-radius: 5px;
                text-decoration: none;
                font-size: 16px;
                transition: transform ease-in-out 0.2s;
            }

            .btn-back-to-list:hover {
                transform: scale(1.05);
            }

            .form-page-container {
                max-width: 800px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
            }

            .form-group input[type="text"],
            .form-group input[type="password"] {
                width: 100%;
                padding: 8px;
                box-sizing: border-box;
            }
            .card-grid {
                margin-top: 60px;
                display: grid;

                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }

            .card-item {
                display: flex;
                flex-direction: column;
                justify-content: space-between;

                height: 100px;
                padding: 20px;

                background-color: #d4d4d4;
                border: 2px solid #595959;
                border-radius: 12px;

                text-decoration: none;
                color: #595959;
                transition: all 0.2s ease;
                position: relative;
            }

            .card-item:hover {
                background-color: #ccccccff;
            }

            .card-top {
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }

            .card-title {
                font-family: 'Fre5';
                font-size: 18px;
                line-height: 1.4;
                color: #37352f;

                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .card-date {
                font-family: 'Fre3';
                font-size: 14px;
                color: #999;
            }

            .del-mode-btn.active {
                border: 2px solid white;
            }

            .del-check-wrapper {
                position: absolute;
                top: 15px;
                right: 15px;
                display: none;
                z-index: 10;
            }

            .del-checkbox {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }

            .gallery-container.delete-mode .del-check-wrapper {
                display: block;
            }

            .del-mode-btn {
                background: url("assets/images/trash.png") no-repeat center center;
                background-size: cover;
                width: 30px;
                height: 30px;
                position: absolute;
                left: 990px;
                top: 30px;
                transition: transform 0.2s ease-in-out;
                cursor: pointer;
                border: none;
            }
            .del-mode-btn:hover {
                transform: scale(1.05);
            }
        </style>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <link
            href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css"
            rel="stylesheet">
        <script
            src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/a11y-dark.min.css">
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/languages/go.min.js"></script>
    </head>
    <body>
        <div class="container">
            <header>
                <a href="#/" class="index-title">放生禁止&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;From
                    the first day to forever.</a>
            </header>

            <div class="side-profile">
                <div class="sp-img"></div>
                <div class="sP-dDay">
                <?php
                    $target_date = new DateTime("2025-07-29");
                    $current_date = new DateTime();

                    $interval = $current_date->diff($target_date);

                    $d_day = $interval->days;

                    if ($current_date > $target_date) {
                        echo "D+" . $d_day+1;
                    } else {
                        echo "D-" . $d_day;
                    }
                ?>
                </div>
                <div class="sP-date">2025.07.29</div>
            </div>

            <div class="side-search"></div>

            <div class="side-login">
                <?php if ($is_admin): ?>
                <div class="input-group" id="logout-btn">
                    <a href="logout.php">Logout</a>
                </div>
            <?php else: ?>
                <form id="login-form" action="ajax_login.php" method="post">

                    <div class="input-group" style="margin-top: 30px;">
                        <input type="text" name="username" required="required" placeholder="ID">
                        <div class="rounded-bar"></div>
                    </div>

                    <div class="input-group">
                        <input type="password" name="password" required="required" placeholder="PW">
                        <div class="rounded-bar"></div>
                    </div>

                    <div class="input-group">
                        <button type="submit">로그인</button>
                        <div id="login-error" style="color:red; margin-top:10px;"></div>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <main id="content"></main>

        </div>
        <script src="assets/js/main.js"></script>
        <script>
            $('#login-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            $('#login-form').hide();

                            $('.side-login').html(
                                `
                        <div class="input-group" id="logout-btn">
                            <a href="logout.php">Logout</a>
                        </div>
                    `
                            );

                            window.location.href = '#/';
                        } else {
                            $('#login-error').text(response.message);
                        }
                    },
                    error: () => $('#login-error').text('로그인 중 서버 오류가 발생했습니다.')
                });
            });
        </script>
    </body>
</html>