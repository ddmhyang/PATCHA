<?php
// common.php : 공통 함수, 설정, 시간 동기화 (최종본)
date_default_timezone_set('Asia/Seoul'); // PHP 한국 시간

// 보안 헤더
header("Content-Security-Policy: default-src * 'self' 'unsafe-inline' 'unsafe-eval' data: gap: content:; style-src * 'self' 'unsafe-inline'; media-src *; img-src * data:;");

// 세션 설정 (24시간 유지)
$lifetime = 86400;
ini_set('session.gc_maxlifetime', $lifetime);
ini_set('session.cookie_lifetime', $lifetime);
session_set_cookie_params(['lifetime' => $lifetime, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);

if (session_status() == PHP_SESSION_NONE) session_start();

// DB 설정 로드
if (!file_exists('config.php')) {
    if (basename($_SERVER['PHP_SELF']) != 'api.php') {
        echo "<script>location.href='setup.php';</script>"; exit;
    } else {
        echo json_encode(['status'=>'error', 'message'=>'시스템 미설치']); exit;
    }
}
require_once 'config.php';

$pdo = get_conn();

// [중요] DB 세션 시간대도 한국으로 강제 고정
$pdo->exec("SET time_zone = '+09:00'");

// ---------------------------------------------------------
// [핵심 함수]
// ---------------------------------------------------------

// 1. 로그 기록 (한국 시간 자동 저장)
function write_log($user_id, $type, $msg) {
    global $pdo;
    try {
        // type: SYSTEM, BATTLE, ITEM, POINT, GAMBLE, STATUS
        $stmt = $pdo->prepare("INSERT INTO School_Log (user_id, type, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $type, $msg]);
    } catch (Exception $e) { /* 로그 에러는 무시 */ }
}

// 2. 상태이상 시간 경과 체크 (진화)
function check_status_evolution() {
    global $pdo;
    // 모든 활성 상태이상 조회
    $list = sql_fetch_all("
        SELECT a.*, i.name, i.max_stage, i.stage_config 
        FROM School_Status_Active a 
        JOIN School_Status_Info i ON a.status_id = i.status_id
    ");

    $now = time();
    foreach($list as $row) {
        if ($row['current_stage'] >= $row['max_stage']) continue; // 이미 최대 단계면 패스

        // 설정 가져오기 (1단계 -> 2단계 시간)
        $cfg = json_decode($row['stage_config'], true);
        $cur = $row['current_stage'];
        
        // 설정이 없으면 기본 300초(5분)
        $req_time = isset($cfg[$cur]['time']) ? intval($cfg[$cur]['time']) : 300;
        $elapsed = $now - strtotime($row['last_evolved_at']);

        if ($elapsed >= $req_time) {
            $next = $cur + 1;
            // 단계 상승 업데이트
            sql_exec("UPDATE School_Status_Active SET current_stage = ?, last_evolved_at = NOW() WHERE id = ?", [$next, $row['id']]);
            
            // 로그 기록
            write_log($row['target_id'], 'STATUS', "상태이상 [{$row['name']}]가 {$next}단계로 악화되었습니다.");
        }
    }
}

// 3. JSON 응답
function json_res($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. SQL 헬퍼
function sql_exec($sql, $params = []) {
    global $pdo;
    try { $stmt = $pdo->prepare($sql); $stmt->execute($params); return true; } 
    catch (Exception $e) { return false; }
}
function sql_fetch($sql, $params = []) {
    global $pdo; $stmt = $pdo->prepare($sql); $stmt->execute($params); return $stmt->fetch(PDO::FETCH_ASSOC);
}
function sql_fetch_all($sql, $params = []) {
    global $pdo; $stmt = $pdo->prepare($sql); $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function sql_one($sql, $params = []) {
    global $pdo; $stmt = $pdo->prepare($sql); $stmt->execute($params); return $stmt->fetchColumn();
}
function h($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function to_int($val) { return intval($val); }
?>