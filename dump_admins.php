<?php
require_once 'includes/db.php';
$stmt = $pdo->query("SELECT * FROM admins");
while($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . " | Username: [" . $row['username'] . "] | Hash: " . $row['password'] . "\n";
}
