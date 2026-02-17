<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

$logs = $pdo->query("
    SELECT l.*, u.name as user_name 
    FROM activity_logs l 
    LEFT JOIN users u ON l.user_id = u.id 
    WHERE action IN ('print_resume', 'download_resume', 'share_resume') 
    ORDER BY l.created_at DESC
")->fetchAll();

// Mock Data for "Default 2 Users" if empty
if (empty($logs)) {
    $logs = [
        [
            'user_name' => 'Demo User 1',
            'action' => 'download_resume',
            'details' => 'Downloaded Resume (PDF)',
            'ip_address' => '127.0.0.1',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'user_name' => 'Demo User 2',
            'action' => 'print_resume',
            'details' => 'Printed Resume',
            'ip_address' => '192.168.1.5',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Downloads - <?php echo SITE_NAME; ?></title>
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
        .admin-nav .nav-links a { color: white; opacity: 0.7; text-decoration: none; padding: 10px 20px; font-weight: 600; border-radius: 12px; }
        .admin-nav .nav-links a:hover, .admin-nav .nav-links a.active { opacity: 1; color: var(--primary-color); background: rgba(255,255,255,0.1); }
        
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
        tr:hover td { background: rgba(0, 242, 255, 0.05); }

        .action-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-print { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid #60a5fa; }
        .badge-download { background: rgba(0, 242, 255, 0.15); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .badge-share { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid #22c55e; }
    </style>
</head>
<body class="home-page">

    <div class="admin-nav">
        <div class="container">
            <a href="dashboard.php" class="logo">Admin<span class="cyan">Panel</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_users.php">Users</a>
                <a href="manage_jobs.php">Jobs</a>
                <a href="view_downloads.php" class="active">Downloads</a>
                <a href="../logout.php" style="color:#fca5a5">Logout</a>
            </div>
        </div>
    </div>

    <main class="container">
        <div class="dashboard-card">
            <h1 style="color:white; margin-bottom:10px;">Resume Activity Log</h1>
            <p style="opacity:0.6; margin-bottom:30px;">Track who is printing, downloading, and sharing resumes.</p>

            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($log['user_name'] ?? 'Public/Guest'); ?></td>
                            <td>
                                <?php 
                                    $action_class = 'badge-secondary';
                                    if(strpos($log['action'], 'print') !== false) $action_class = 'badge-print';
                                    if(strpos($log['action'], 'download') !== false) $action_class = 'badge-download';
                                    if(strpos($log['action'], 'share') !== false) $action_class = 'badge-share';
                                ?>
                                <span class="action-badge <?php echo $action_class; ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $log['action'])); ?>
                                </span>
                            </td>
                            <td style="opacity:0.8; font-size:0.9rem;"><?php echo htmlspecialchars($log['details']); ?></td>
                            <td style="opacity:0.6; font-family:monospace;"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td style="opacity:0.7; font-size:0.85rem;"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
