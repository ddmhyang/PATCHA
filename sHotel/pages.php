<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>sHotel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <h1><a href="index.php">sHotel</a></h1>
        <nav>
            <a href="index.php?page=main">Main</a>
            <a href="index.php?page=gallery">Gallery</a>
            <a href="index.php?page=etc">Etc</a>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="content">
        <?php
        $page = $_GET['page'] ?? 'main';
        $file = $page . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            include 'main.php';
        }
        ?>
    </main>
</body>
</html>