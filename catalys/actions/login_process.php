<?php
require_once 'db.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'];
    $admin_pass = $_POST['admin_pass'];

    $sql = "SELECT * FROM admins WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($admin_pass, $admin['admin_pass'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            $response['success'] = true;
        }
    }
}

echo json_encode($response);
?>