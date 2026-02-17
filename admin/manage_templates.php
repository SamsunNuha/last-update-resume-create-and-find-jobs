<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';
require_once '../includes/template_config.php'; // Load dynamic template definition

// $templates is now available from the config file
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Templates - <?php echo SITE_NAME; ?></title>
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
        
        .template-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; margin-top: 40px; }
        
        .template-card { 
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 24px; 
            overflow: hidden; 
            transition: all 0.3s; 
        }
        .template-card:hover { transform: translateY(-8px); border-color: var(--primary-color); box-shadow: 0 15px 30px rgba(0, 242, 255, 0.1); }
        .template-img { height: 200px; background: rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; color: var(--primary-color); overflow:hidden; }
        
        .template-info { padding: 25px; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-Pro { background: rgba(0, 242, 255, 0.15); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .badge-Free { background: rgba(255, 255, 255, 0.05); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.1); }
        
        h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }
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
                <a href="manage_templates.php" class="active">Templates</a>
                <a href="view_downloads.php">Downloads</a>
                <a href="../logout.php" style="color:#fca5a5">Logout</a>
            </div>
        </div>
    </div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_users.php">Users</a>
                <a href="manage_jobs.php">Jobs</a>
                <a href="manage_templates.php" class="active">Templates</a>
                <a href="view_downloads.php">Downloads</a>
                <a href="../logout.php" style="color:#fca5a5">Logout</a>
            </div>
        </div>
    </div>

    <main class="container">
        <h1>Resume Templates</h1>
        <p>Overview of available resume templates (Managed via <code>includes/template_config.php</code>)</p>

        <div class="template-grid">
            <?php foreach ($templates as $id => $t): ?>
                <div class="template-card">
                    <div class="template-img">
                         <!-- Placeholder or Real Image -->
                         <?php if(isset($t['image']) && $t['image']): ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background: #e5e7eb;">
                                <span style="font-size:3rem;">📄</span>
                            </div>
                         <?php else: ?>
                            <span>Preview Image</span>
                         <?php endif; ?>
                    </div>
                    <div class="template-info">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h3 style="margin: 0; font-size: 1.1rem; color: #111;"><?php echo htmlspecialchars($t['name']); ?></h3>
                            <span class="badge badge-<?php echo $t['type']; ?>"><?php echo $t['type']; ?></span>
                        </div>
                        <p style="color:#666; font-size:0.85rem; margin:0 0 10px 0; min-height: 40px;">
                            <?php echo htmlspecialchars($t['description']); ?>
                        </p>
                        <div style="font-size:0.8rem; color:#999;">
                            ID: <strong><?php echo $id; ?></strong> | File: <code><?php echo $t['file']; ?></code>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>
