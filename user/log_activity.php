<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $details = $data['details'] ?? '';
    $user_id = $_SESSION['user_id'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    if ($action) {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $action, $details, $ip_address])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing action']);
    }
} else {
    http_response_code(405); // Method Not Allowed
}
?>
