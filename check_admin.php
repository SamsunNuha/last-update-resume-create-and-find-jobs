<?php
require_once 'includes/db.php';

$username = 'admin@gmail.com';
$password = 'sams';

echo "Checking credentials for user: $username\n";

$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin) {
    echo "User found in database.\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Stored Hash: " . $admin['password'] . "\n";
    
    if (password_verify($password, $admin['password'])) {
        echo "SUCCESS: Password matches!\n";
    } else {
        echo "FAILURE: Password does NOT match.\n";
        echo "Updating password to '$password'...\n";
        
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        if ($updateStmt->execute([$newHash, $admin['id']])) {
            echo "Password updated successfully.\n";
        } else {
            echo "Failed to update password.\n";
        }
    }
} else {
    echo "User '$username' NOT found in database.\n";
    echo "Creating user '$username' with password '$password'...\n";
    
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $insertStmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    if ($insertStmt->execute([$username, $newHash])) {
        echo "User created successfully.\n";
    } else {
        echo "Failed to create user.\n";
    }
}
?>
