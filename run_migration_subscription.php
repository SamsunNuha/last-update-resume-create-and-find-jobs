<?php
require_once 'includes/db.php';

try {
    $sql = file_get_contents('migrate_subscription.sql');
    $pdo->exec($sql);
    echo "Migration successful: 'account_type' and 'subscription_end' columns updated/added to 'users' table.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
?>
