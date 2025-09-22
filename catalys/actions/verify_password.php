<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $board = $_POST['board'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $password = $_POST['password'] ?? '';
    
    $table_name = 'posts_' . $board;
    $allowed_tables = ['posts_for', 'posts_log', 'posts_sp', 'posts_etc'];
    if (!in_array($table_name, $allowed_tables)) {
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT password_hash FROM {$table_name} WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stored_hash = $result->fetch_assoc()['password_hash'];
        
        if ($stored_hash && password_verify($password, $stored_hash)) {
            $_SESSION['secret_access'][$id] = true;
            $response['success'] = true;
        }
    }
}

echo json_encode($response);
?>