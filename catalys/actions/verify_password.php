<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board = $_POST['board'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $sql = "SELECT setting_value FROM settings WHERE setting_key = 'secret_password'";
    $result = $conn->query($sql);
    $stored_hash = $result->fetch_assoc()['setting_value'];
    if (password_verify($password, $stored_hash)) {
        $_SESSION['secret_access'][$id] = true;
        $response['success'] = true;
    }
}

echo json_encode($response);
?>