<?php
/*
 * register_admin.php (일회용 스크립트)
 * 관리자 계정을 DB에 등록합니다.
 */

include 'db_connect.php'; // DB 연결

// ★★★ 원하는 관리자 아이디와 비밀번호로 변경하세요 ★★★
$admin_username = "admin";
$admin_password = "your_secure_password"; // ★★★ 매우 강력한 비번으로 바꾸세요!

// PHP의 최신 표준 암호화 방식 (Bcrypt)
$password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO youth_admin_users (username, password_hash) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_username, $password_hash]);

    echo "<h1>관리자 계정 등록 성공!</h1>";
    echo "<p>아이디: {$admin_username}</p>";
    echo "<p>이제 이 'register_admin.php' 파일은 서버에서 즉시 삭제하세요!</p>";

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "<h1>오류: 이미 등록된 아이디입니다.</h1>";
    } else {
        echo "<h1>DB 오류: " . $e->getMessage() . "</h1>";
    }
}
?>