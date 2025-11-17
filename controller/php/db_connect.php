<?php
$db_host = "localhost";
$db_name = "z3rdk9";
$db_user = "z3rdk9";
$db_pass = "Rkwhr1027hyun!";

// PDO (PHP Data Objects)를 사용한 연결 방식
// (보안이 좋고 현대적인 방식입니다.)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

// 옵션: 에러가 났을 때 예외(Exception)를 발생시킴
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // DB 연결 시도
     $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
     // 연결 실패 시 에러 메시지 출력
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>