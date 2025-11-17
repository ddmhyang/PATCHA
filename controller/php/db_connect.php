<?php
/*
 * db_connect.php
 * (★★★ SQLite 버전 ★★★)
 * 데이터베이스 연결을 설정합니다.
 */

// 1. 데이터베이스 파일 이름 정의
// 이 파일이 모든 데이터를 담게 됩니다.
$db_file = __DIR__ . '/database.db';

// 2. PDO (PHP Data Objects)를 사용한 SQLite 연결
$dsn = "sqlite:" . $db_file;

// 옵션: 에러가 났을 때 예외(Exception)를 발생시킴
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // DB 연결 시도
     $pdo = new PDO($dsn, null, null, $options);
     
     // (★중요★) SQLite는 동시 접근 성능을 위해 WAL 모드를 켜는 것이 좋습니다.
     $pdo->exec('PRAGMA journal_mode = wal;');
     
     // (★중요★) 외래 키(Foreign Key) 제약 조건을 활성화합니다.
     $pdo->exec('PRAGMA foreign_keys = ON;');
     
} catch (\PDOException $e) {
     // 연결 실패 시 에러 메시지 출력
     throw new \PDOException($e->getMessage(), (int)$e.getCode());
}
?>