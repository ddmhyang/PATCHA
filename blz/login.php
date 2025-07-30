<?php
// /blz/login.php (최종 수정본 - 콘텐츠 조각)
require_once 'includes/db.php';
?>
<div class="page-header">
    <div class="page-title">Login</div>
    <div class="page-divider"></div>
</div>

<div class="login-form-container">
    <form id="login-form" action="ajax_login.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">LOGIN</button>
    </form>
    <div id="login-error" class="error"></div>
</div>