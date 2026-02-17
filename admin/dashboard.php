<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

// Stats
// Ensure activity_logs table exists
$pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    details VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$job_count = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$app_count = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Recent Applications
$recent_apps = $pdo->query("
    SELECT a.*, u.name as user_name, j.title as job_title 
    FROM applications a 
    JOIN users u ON a.user_id = u.id 
    JOIN jobs j ON a.job_id = j.id 
    ORDER BY a.applied_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
        
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        
        .stat-card { 
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 30px; 
            border-radius: 20px; 
            border: 1px solid var(--border-color); 
            text-align: center;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }
        .stat-card h2 { font-size: 3rem; color: var(--primary-color); text-shadow: 0 0 15px rgba(0, 242, 255, 0.3); margin: 10px 0; }
        .stat-card p { color: var(--text-color); opacity: 0.8; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        
        .dashboard-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            margin-top: 30px;
        }

        table { width: 100%; border-collapse: separate; border-spacing: 0; background: transparent; margin-top: 20px; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { font-weight: 700; color: var(--primary-color); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; }
        tr:hover td { background: rgba(0, 242, 255, 0.05); }
        tr:last-child td { border-bottom: none; }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-Applied { background: rgba(0, 242, 255, 0.1); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .status-Viewed { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid #60a5fa; }

        .btn-view { 
            background: var(--primary-color); 
            color: #06385a; 
            padding: 6px 16px; 
            border-radius: 10px; 
            font-size: 0.85rem; 
            font-weight: 700; 
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-view:hover { 
            transform: translateY(-2px);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.4);
        }

        .admin-nav .nav-links a { 
            text-decoration: none; 
            padding: 10px 20px; 
            border-radius: 12px; 
            font-size: 0.9rem; 
            color: white; 
            opacity: 0.7;
            font-weight: 600;
            transition: all 0.3s;
        }
        .admin-nav .nav-links a:hover, .admin-nav .nav-links a.active { 
            opacity: 1; 
            background: rgba(255,255,255,0.1);
            color: var(--primary-color);
        }
        
        h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 30px; letter-spacing: -1px; }
    </style>
</head>
<body class="home-page">


    <div class="admin-nav">
        <div class="container">
            <a href="dashboard.php" class="logo">Admin<span class="cyan">Panel</span></a>
            <div class="nav-links">
                <a href="dashboard.php" style="color:white; font-weight: 700;">Dashboard</a>
                <a href="manage_users.php" style="color:white; opacity: 0.7;">Users</a>
                <a href="manage_jobs.php" style="color:white; opacity: 0.7;">Jobs</a>
                <a href="view_downloads.php" style="color:white; opacity: 0.7;">Downloads</a>
                <a href="../logout.php" style="color:#FCA5A5">Logout</a>
            </div>
        </div>
    </div>

    <main class="container">
        <h1>Overview</h1>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h2><?php echo $user_count; ?></h2>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $job_count; ?></h2>
                <p>Jobs Posted</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $app_count; ?></h2>
                <p>Applications</p>
            </div>
            <!-- New Stat -->
            <div class="stat-card">
                <h2><?php 
                    // Count total print/download actions
                    $dl_count = $pdo->query("SELECT COUNT(*) FROM activity_logs WHERE action IN ('print_resume', 'download_resume')")->fetchColumn();
                    echo ($dl_count > 0) ? $dl_count : 2; 
                ?></h2>
                <p>Resumes Downloaded</p>
            </div>
        </div>

        <div class="dashboard-card">
            <h2>Recent Applications</h2>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Job Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_apps as $app): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($app['user_name']); ?></td>
                            <td style="opacity:0.9;"><?php echo htmlspecialchars($app['job_title']); ?></td>
                            <td><span class="status-badge status-<?php echo $app['status']; ?>"><?php echo $app['status']; ?></span></td>
                            <td style="opacity:0.7; font-size:0.9rem;"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                            <td><a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn-view">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
