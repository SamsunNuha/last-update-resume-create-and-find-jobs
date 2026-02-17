<?php
// Debug script to check session and user data
session_start();
require_once 'includes/db.php';

header('Content-Type: application/json');

$debug = [];

// Check session
$debug['session_id'] = session_id();
$debug['user_id_in_session'] = $_SESSION['user_id'] ?? 'NOT SET';
$debug['user_name_in_session'] = $_SESSION['user_name'] ?? 'NOT SET';

// Check if user exists in database
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $debug['user_in_database'] = 'YES';
        $debug['user_data'] = $user;
    } else {
        $debug['user_in_database'] = 'NO - USER DOES NOT EXIST!';
        $debug['error'] = 'Session has user_id=' . $_SESSION['user_id'] . ' but this user does not exist in the users table';
    }
}

// Check resumes table schema
$stmt = $pdo->query("SHOW COLUMNS FROM resumes");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
$debug['resumes_columns'] = $columns;

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
