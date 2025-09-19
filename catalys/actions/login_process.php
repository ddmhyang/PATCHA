<?php
// PHP 오류가 JSON 응답을 깨는 것을 방지합니다.
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// --- 경로 수정 ---
// actions 폴더에서 한 단계 위로 올라가 includes 폴더의 db.php를 찾도록 경로를 수정했습니다.
require_once __DIR__ . '/../includes/db.php';

$response = ['success' => false, 'message' => '알 수 없는 오류가 발생했습니다.'];

// 데이터베이스 연결이 성공했는지 확인
if (!isset($conn) || !$conn) {
    $response['message'] = '데이터베이스 연결에 실패했습니다. includes/db.php 파일을 확인해주세요.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['admin_id']) || !isset($_POST['admin_pass'])) {
        $response['message'] = 'ID 또는 비밀번호가 전송되지 않았습니다.';
        echo json_encode($response);
        exit();
    }

    $admin_id = $_POST['admin_id'];
    $admin_pass = $_POST['admin_pass'];

    $sql = "SELECT * FROM admins WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('s', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($admin_pass, $admin['admin_pass'])) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $response['success'] = true;
                $response['message'] = '로그인 성공';
            } else {
                $response['message'] = '비밀번호가 일치하지 않습니다.';
            }
        } else {
            $response['message'] = '존재하지 않는 아이디입니다.';
        }
        $stmt->close();
    } else {
        // 쿼리 준비 실패 시
        $response['message'] = '로그인 처리 중 오류가 발생했습니다.';
    }
}

$conn->close();
echo json_encode($response);
exit();
?>