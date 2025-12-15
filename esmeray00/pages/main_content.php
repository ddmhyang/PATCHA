<?php
require_once '../includes/db.php';
$page_slug = '';

$stmt = $mysqli->prepare("SELECT content FROM pages WHERE slug = ?");
$stmt->bind_param("s", $page_slug);
$stmt->execute();
$page_content = $stmt->get_result()->fetch_assoc()['content'] ?? '<p>메인 페이지에 오신 것을 환영합니다.</p>';
$stmt->close();
?>
<div class="page-container">
    <div class="main_back1">
        <nav>
            <a class="btn_gallery" href="#/gallery"></a>
            <a class="btn_profile" href="#/profile"></a>
            <a class="btn_guest_book" href="#/guest_book"></a>
            <?php if ($is_admin): ?>
                <a class="btn_login" href="logout.php"></a>
            <?php else: ?>
                <a class="btn_login" href="#/login"></a>
        </nav>
    </div>
</div>
<?php endif; ?>