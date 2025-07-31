<?php
session_start();
if (!isset($_SESSION['blz_logged_in']) || $_SESSION['blz_logged_in'] !== true) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        die('권한이 없습니다.');
    } else {
        header('Location: ../index.php');
    }
    exit;
}
?>
<?php
require_once '../includes/db.php';
?>
<div class="login-form-container">
    <form id="login-form" action="ajax_login.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">LOGIN</button>
    </form>
    <div id="login-error" class="error"></div>
</div>