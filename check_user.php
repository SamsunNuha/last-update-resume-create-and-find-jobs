<?php
require_once 'includes/db.php';

$email = 'admin@gmail.com';

echo "Checking if '$email' exists in 'users' table...\n";

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "User found in 'users' table (ID: " . $user['id'] . ").\n";
} else {
    echo "User NOT found in 'users' table.\n";
}
?>
