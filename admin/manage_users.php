<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

// Handle Actions
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    $msg = "User deleted successfully.";
}

if (isset($_GET['promote'])) {
    $id = $_GET['promote'];
    $pdo->prepare("UPDATE users SET account_type = 'pro' WHERE id = ?")->execute([$id]);
    $msg = "User promoted to Pro.";
}

if (isset($_GET['demote'])) {
    $id = $_GET['demote'];
    $pdo->prepare("UPDATE users SET account_type = 'free' WHERE id = ?")->execute([$id]);
    $msg = "User demoted to Free.";
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=91.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .admin-nav { 
            background: rgba(13, 85, 116, 0.8) !important;
            backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(0, 242, 255, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .admin-nav .container { display: flex; justify-content: space-between; align-items: center; }
        .admin-nav .logo { 
            font-weight: 850; 
            font-size: 1.5rem; 
            color: white; 
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .admin-nav .logo .cyan { color: var(--primary-color); text-shadow: 0 0 15px rgba(0, 242, 255, 0.4); }
        .admin-nav .nav-links a { 
            text-decoration: none; 
            padding: 10px 20px; 
            border-radius: 12px; 
            font-size: 0.9rem; 
            color: white; 
            opacity: 0.7;
            font-weight: 600;
        }
        .admin-nav .nav-links a.active { opacity: 1; background: rgba(255,255,255,0.1); color: var(--primary-color); }
        
        .dashboard-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            margin-top: 30px;
        }

        table { width: 100%; border-collapse: separate; border-spacing: 0; background: transparent; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { color: var(--primary-color); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; font-weight: 700; }
        
        .badge { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-pro { background: rgba(0, 242, 255, 0.15); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .badge-free { background: rgba(255, 255, 255, 0.05); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.1); }

        .btn-sm { padding: 8px 14px; font-size: 0.8rem; border-radius: 10px; text-decoration: none; margin-right: 5px; font-weight: 700; transition: all 0.3s; }
        .btn-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .btn-danger:hover { background: #ef4444; color: white; }
        .btn-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .btn-success:hover { background: #22c55e; color: white; }
    </style>
</head>
<body class="home-page">

    <div class="admin-nav">
        <div class="container">
            <a href="dashboard.php" class="logo">Admin<span class="cyan">Panel</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_users.php" class="active">Users</a>
                <a href="manage_jobs.php">Jobs</a>
                <a href="view_downloads.php">Downloads</a>
                <a href="../logout.php" style="color:#fca5a5">Logout</a>
            </div>
        </div>
    </div>

    <main class="container">
        <div class="dashboard-card">
            <h1>Manage Users</h1>
            <?php if (isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($u['name']); ?></td>
                            <td style="opacity:0.8;"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $u['account_type']; ?>">
                                    <?php echo ucfirst($u['account_type']); ?>
                                </span>
                            </td>
                            <td style="opacity:0.7; font-size:0.9rem;"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['account_type'] == 'free'): ?>
                                    <a href="?promote=<?php echo $u['id']; ?>" class="btn-sm btn-success">Make Pro</a>
                                <?php else: ?>
                                    <a href="?demote=<?php echo $u['id']; ?>" class="btn-sm btn-danger">Make Free</a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $u['id']; ?>" class="btn-sm btn-danger" onclick="return confirm('Delete user?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
