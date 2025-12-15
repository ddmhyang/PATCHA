<?php
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>esmeray00</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        <?php if ($is_admin): ?>
        .guestbook-item:active {
            background: rgba(161, 0, 0, 0.3);
        }
        <?php endif; ?>
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="guestbook-container">
    <div class="guestbook-title">Guest Book</div>
    <div class="guestbook-bar"></div>

    <div class="write-form">
        <input type="text" id="gb_name" placeholder="닉네임" maxlength="20">
        <textarea id="gb_content" placeholder="방명록을 남겨주세요"></textarea>
        <button class="submit-btn" onclick="saveGuestbook()">남기기</button>
    </div>

    <div class="guestbook-list" id="guestbook_list">
        </div>
</div>

<script>
    $(document).ready(function(){
        loadGuestbook();

        <?php if ($is_admin): ?>
        let pressTimer;

        $(document).on('mousedown touchstart', '.guestbook-item', function(e) {
            let item = $(this);
            let id = item.data('id');

            pressTimer = window.setTimeout(function() {
                if(confirm('이 방명록을 삭제하시겠습니까?')) {
                    deleteGuestbook(id, item);
                }
            }, 800);
        });

        $(document).on('mouseup mouseleave touchend touchmove', '.guestbook-item', function() {
            clearTimeout(pressTimer);
        });
        <?php endif; ?>
    });

    function saveGuestbook() {
        let name = $("#gb_name").val().trim();
        let content = $("#gb_content").val().trim();

        if(!name || !content) {
            alert("닉네임과 내용을 입력해주세요.");
            return;
        }

        $.ajax({
            url: "ajax_save_guestbook.php",
            type: "POST",
            data: { name: name, content: content },
            success: function(response) {
                if(response.trim() === "success") {
                    $("#gb_name").val("");
                    $("#gb_content").val("");
                    loadGuestbook();
                } else {
                    alert("오류가 발생했습니다: " + response);
                }
            }
        });
    }

    function loadGuestbook() {
        $.ajax({
            url: "ajax_list_guestbook.php",
            type: "GET",
            success: function(html) {
                $("#guestbook_list").html(html);
            }
        });
    }

    function deleteGuestbook(id, itemElement) {
        $.ajax({
            url: "ajax_delete_guestbook.php",
            type: "POST",
            data: { id: id },
            success: function(response) {
                if(response.trim() === "success") {
                    itemElement.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert("삭제 실패: " + response);
                }
            }
        });
    }
</script>

</body>
</html>