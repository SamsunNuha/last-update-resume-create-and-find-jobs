<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

$stmt = $pdo->prepare("
    SELECT a.*, j.title as job_title, j.company 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    WHERE a.user_id = ? 
    ORDER BY a.applied_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=71.0">

    <style>
        .app-list { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px; 
            border: 1px solid var(--border-color); 
            overflow: hidden; 
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }
        .app-item { 
            padding: 25px; 
            border-bottom: 1px solid var(--border-color); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: background 0.3s;
        }
        .app-item:hover { background: rgba(0, 242, 255, 0.03); }
        .app-item:last-child { border-bottom: none; }
        .app-info h3 { margin-bottom: 8px; color: var(--primary-color); font-weight: 700; font-size: 1.2rem; }
        .app-info p { color: var(--text-color); opacity: 0.9; margin-bottom: 5px; }
        .app-date { color: var(--text-muted); font-size: 0.85rem; font-weight: 500; }
        .status-badge { padding: 8px 18px; border-radius: 30px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-Applied { background: rgba(0, 162, 255, 0.15); color: var(--secondary-color); border: 1px solid var(--secondary-color); }
        .status-Viewed { background: rgba(0, 242, 255, 0.1); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .status-Shortlisted { background: rgba(0, 242, 255, 0.2); color: var(--primary-color); border: 1px solid var(--primary-color); box-shadow: 0 0 15px rgba(0, 242, 255, 0.2); }
        .status-Rejected { background: rgba(255, 46, 46, 0.1); color: var(--error-color); border: 1px solid var(--error-color); }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <h1>Track Your Applications</h1>
        <p>Keep an eye on your career progress</p>

        <div class="app-list">
            <?php if (empty($applications)): ?>
                <div style="padding: 40px; text-align: center; color: #666;">
                    You haven't applied for any jobs yet. <br>
                    <a href="jobs.php" class="btn btn-primary" style="margin-top:20px; width:auto">Browse Jobs</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): ?>
                    <div class="app-item">
                        <div class="app-info">
                            <h3><?php echo htmlspecialchars($app['job_title']); ?></h3>
                            <p><?php echo htmlspecialchars($app['company']); ?></p>
                            <span class="app-date">Applied on: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?></span>
                        </div>
                        <div class="status-badge status-<?php echo $app['status']; ?>">
                            <?php echo $app['status']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>


</body>
</html>
