<?php
session_start();
require_once '../includes/db.php';

echo "<!DOCTYPE html><html><head><title>Admin Debug</title><style>
    body { background: #05080a; color: #00f2ff; font-family: monospace; padding: 20px; }
    .box { border: 1px solid #00f2ff; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
    h2 { color: #fff; border-bottom: 1px solid #333; padding-bottom: 5px; }
    .status-ok { color: #00ff00; }
    .status-err { color: #ff0000; }
</style></head><body>";

echo "<h1>Admin Login Diagnostics</h1>";

// 1. Session Check
echo "<div class='box'><h2>1. Session Diagnostics</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Current File: " . __FILE__ . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Session Save Path: " . ini_get('session.save_path') . "<br>";
if (isset($_SESSION['admin_id'])) {
    echo "Active Admin Session: " . $_SESSION['admin_id'] . " (User: " . $_SESSION['admin_user'] . ")<br>";
} else {
    echo "No Admin Session Active.<br>";
}
echo "</div>";

// 2. Database Check
echo "<div class='box'><h2>2. Database Diagnostics</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    $count = $stmt->fetchColumn();
    echo "<span class='status-ok'>Database Connection: OK</span><br>";
    echo "Total Admins found: $count<br>";
} catch (Exception $e) {
    echo "<span class='status-err'>Database Connection Error: " . $e->getMessage() . "</span><br>";
}
echo "</div>";

// 3. User Check
$testUser = 'admin@gmail.com';
$testPass = 'sams';
echo "<div class='box'><h2>3. Credential Test ($testUser)</h2>";
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$testUser]);
$admin = $stmt->fetch();

if ($admin) {
    echo "User '$testUser' found in database.<br>";
    echo "Stored Hash: " . $admin['password'] . "<br>";
    if (password_verify($testPass, $admin['password'])) {
        echo "<span class='status-ok'>Password check for '$testPass': SUCCESS</span><br>";
    } else {
        echo "<span class='status-err'>Password check for '$testPass': FAILED</span><br>";
        echo "Suggestion: Use the 'Reset Admin' button below to sync with 'sams'.<br>";
    }
} else {
    echo "<span class='status-err'>User '$testUser' NOT found in database.</span><br>";
    echo "Available users: ";
    $all = $pdo->query("SELECT username FROM admins")->fetchAll(PDO::FETCH_COLUMN);
    echo implode(", ", $all) . "<br>";
}
echo "</div>";

// 4. Action: Force Sync/Reset
if (isset($_GET['reset'])) {
    echo "<div class='box'><h2>Reset Action</h2>";
    $newHash = password_hash($testPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = ?");
    // Manual check for id=1 if username isn't unique index but let's try direct update first
    $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ? WHERE id = 1");
    if (!$stmt->execute([$testUser, $newHash]) || $stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute([$testUser, $newHash]);
    }
    echo "Admin credentials forced to: <b>$testUser / $testPass</b><br>";
    echo "<a href='debug_login.php' style='color:yellow;'>Refresh to test</a>";
    echo "</div>";
}

echo "<div style='margin-top:20px;'>";
echo "<a href='debug_login.php?reset=1' style='background:#f00; color:#fff; padding:10px; text-decoration:none; border-radius:5px;'>FORCE RESET ADMIN (admin@gmail.com / sams)</a> &nbsp;";
echo "<a href='login.php' style='background:#00f2ff; color:#000; padding:10px; text-decoration:none; border-radius:5px;'>GO TO LOGIN PAGE</a>";
echo "</div>";

echo "</body></html>";
