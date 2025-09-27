<form id="login-form">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>

<script>
$('#login-form').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: 'ajax_login.php',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            let res = JSON.parse(response);
            if(res.status === 'success') {
                window.location.href = 'index.php?page=main';
            } else {
                alert(res.message);
            }
        }
    });
});
</script>