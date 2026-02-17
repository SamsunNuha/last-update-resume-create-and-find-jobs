<?php
// Enhanced debug script to check everything
session_start();
require_once 'includes/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Report</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .section { background: #252526; padding: 15px; margin: 10px 0; border-left: 3px solid #007acc; }
        .error { color: #f48771; }
        .success { color: #4ec9b0; }
        .warning { color: #dcdcaa; }
        h2 { color: #4ec9b0; margin-top: 0; }
        pre { background: #1e1e1e; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Resume Builder Debug Report</h1>
    
    <div class="section">
        <h2>1. Session Status</h2>
        <pre><?php
        echo "Session ID: " . session_id() . "\n";
        echo "User ID in session: " . ($_SESSION['user_id'] ?? '<span class="error">NOT SET</span>') . "\n";
        echo "User Name in session: " . ($_SESSION['user_name'] ?? '<span class="error">NOT SET</span>') . "\n";
        ?></pre>
    </div>

    <div class="section">
        <h2>2. User Verification</h2>
        <pre><?php
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id, name, email, account_type FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                echo '<span class="success">✓ User EXISTS in database</span>' . "\n";
                echo "ID: " . $user['id'] . "\n";
                echo "Name: " . $user['name'] . "\n";
                echo "Email: " . $user['email'] . "\n";
                echo "Account Type: " . $user['account_type'] . "\n";
            } else {
                echo '<span class="error">✗ User DOES NOT EXIST in database!</span>' . "\n";
                echo '<span class="error">Session user_id=' . $_SESSION['user_id'] . ' not found in users table</span>' . "\n";
            }
        } else {
            echo '<span class="error">✗ No user_id in session - not logged in</span>';
        }
        ?></pre>
    </div>

    <div class="section">
        <h2>3. Resumes Table Schema</h2>
        <pre><?php
        $stmt = $pdo->query("DESCRIBE resumes");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $required = ['portfolio', 'updated_at', 'is_locked'];
        foreach ($required as $col) {
            $exists = false;
            foreach ($columns as $c) {
                if ($c['Field'] === $col) {
                    $exists = true;
                    break;
                }
            }
            echo ($exists ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . " Column '$col' " . ($exists ? 'exists' : 'MISSING') . "\n";
        }
        
        echo "\nAll columns:\n";
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
        ?></pre>
    </div>

    <div class="section">
        <h2>4. Existing Resume Check</h2>
        <pre><?php
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id, full_name, email, created_at FROM resumes WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $resume = $stmt->fetch();
            
            if ($resume) {
                echo '<span class="success">✓ Resume EXISTS for this user</span>' . "\n";
                echo "Resume ID: " . $resume['id'] . "\n";
                echo "Full Name: " . $resume['full_name'] . "\n";
                echo "Email: " . $resume['email'] . "\n";
                echo "Created: " . $resume['created_at'] . "\n";
            } else {
                echo '<span class="warning">⚠ No resume found (will INSERT new)</span>';
            }
        }
        ?></pre>
    </div>

    <div class="section">
        <h2>5. Test Save Simulation</h2>
        <pre><?php
        if (isset($_SESSION['user_id']) && $user) {
            echo "Simulating save_draft.php logic...\n\n";
            
            try {
                // Test if we can prepare the INSERT statement
                $sql = "INSERT INTO resumes (user_id, full_name, email, phone, website, portfolio, address, summary, education, experience, skills, projects, certifications, template_id, bank_name, branch_name, acc_no, acc_name, extra_details) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                echo '<span class="success">✓ INSERT statement prepared successfully</span>' . "\n";
                
                // Test UPDATE
                $sql = "UPDATE resumes SET full_name=?, email=?, phone=?, website=?, portfolio=?, address=?, summary=?, education=?, experience=?, skills=?, projects=?, certifications=?, template_id=?, bank_name=?, branch_name=?, acc_no=?, acc_name=?, extra_details=?, updated_at=NOW() WHERE user_id=?";
                $stmt = $pdo->prepare($sql);
                echo '<span class="success">✓ UPDATE statement prepared successfully</span>' . "\n";
                
            } catch (PDOException $e) {
                echo '<span class="error">✗ SQL Error: ' . $e->getMessage() . '</span>';
            }
        }
        ?></pre>
    </div>

    <div class="section">
        <h2>6. Recommended Actions</h2>
        <pre><?php
        if (!isset($_SESSION['user_id'])) {
            echo '<span class="error">→ You are NOT logged in. Please login first.</span>';
        } elseif (!$user) {
            echo '<span class="error">→ Your session user_id does not exist. Click logout and login again.</span>';
        } else {
            echo '<span class="success">→ Everything looks good! Try saving in the resume builder now.</span>';
        }
        ?></pre>
    </div>

    <p><a href="resume_builder.php" style="color: #4ec9b0;">← Back to Resume Builder</a></p>
</body>
</html>
