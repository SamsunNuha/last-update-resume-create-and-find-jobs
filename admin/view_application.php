<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT a.*, u.name as user_name, j.title as job_title, r.full_name as resume_name, r.id as res_id, r.photo
    FROM applications a 
    JOIN users u ON a.user_id = u.id 
    JOIN jobs j ON a.job_id = j.id
    JOIN resumes r ON a.resume_id = r.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$app = $stmt->fetch();

if (!$app) die("Application not found.");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    $app['status'] = $status;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application - <?php echo $app['user_name']; ?></title>
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
        .nav-links a { color: white; opacity: 0.7; text-decoration: none; padding: 10px 20px; font-weight: 600; border-radius: 12px; transition: all 0.3s; }
        .nav-links a:hover { opacity: 1; background: rgba(255,255,255,0.1); color: var(--primary-color); }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); font-weight: 700; text-decoration: none; margin-bottom: 25px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .dashboard-card { 
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 35px; 
            border-radius: 28px; 
            border: 1px solid var(--border-color);
        }
        .dashboard-card h3 { margin-bottom: 25px; color: var(--primary-color); border-bottom: 2px solid var(--primary-color); display: inline-block; padding-bottom: 5px; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .response-box {
            background: rgba(0, 242, 255, 0.05);
            padding: 25px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            margin-bottom: 25px;
        }
        .response-q { font-weight: 800; color: var(--primary-color); margin-bottom: 8px; font-size: 0.85rem; text-transform: uppercase; }
        .response-a { font-size: 1.1rem; color: white; line-height: 1.4; }

        .form-control {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--border-color);
            color: white;
            border-radius: 12px;
            padding: 12px;
            font-family: inherit;
        }
    </style>
</head>
<body class="home-page">

    <div class="admin-nav" style="margin-bottom: 50px;">
        <div class="container">
            <a href="dashboard.php" class="logo">Admin<span class="cyan">Panel</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Stats</a>
                <a href="manage_jobs.php">Jobs</a>
                <a href="../logout.php" style="color:#FCA5A5">Logout</a>
            </div>
        </div>
    </div>

    <main class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Application Details</h1>

        <div class="dashboard-grid" style="margin-top: 20px;">
            <div class="dashboard-card">
                <h3>Candidate Info</h3>
                <?php if ($app['photo']): ?>
                    <img src="../uploads/resumes/<?php echo $app['photo']; ?>" alt="Candidate Photo" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; margin-bottom: 20px; border: 2px solid var(--border-color);">
                <?php endif; ?>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($app['user_name']); ?></p>
                <p><strong>Job:</strong> <?php echo htmlspecialchars($app['job_title']); ?></p>
                <p><strong>Applied on:</strong> <?php echo date('M d, Y', strtotime($app['applied_at'])); ?></p>
                <p style="margin-top: 15px;">
                    <a href="../user/resume_preview.php?user_id=<?php echo $app['user_id']; ?>" target="_blank" class="btn btn-secondary" style="width: auto; padding: 8px 15px; font-size: 0.85rem;">📄 View Full Resume</a>
                </p>
                
                <form method="POST" style="margin-top: 20px;">
                    <label><strong>Status:</strong></label>
                    <select name="status" class="form-control" style="width: 100%; margin: 10px 0; padding: 10px; border-radius: 8px;">
                        <option value="Applied" <?php echo $app['status'] == 'Applied' ? 'selected' : ''; ?>>Applied</option>
                        <option value="Viewed" <?php echo $app['status'] == 'Viewed' ? 'selected' : ''; ?>>Viewed</option>
                        <option value="Shortlisted" <?php echo $app['status'] == 'Shortlisted' ? 'selected' : ''; ?>>Shortlisted</option>
                        <option value="Rejected" <?php echo $app['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </form>
            </div>

            <div class="dashboard-card" style="margin-top: 20px; grid-column: 1 / -1;">
                <h3>Specific Application Details</h3>
                <div class="response-box">
                    <?php 
                    $responses = json_decode($app['form_responses'] ?? '[]', true);
                    if (empty($responses)): ?>
                        <p style="color:var(--text-color); opacity:0.6; margin:0">No specific questions were asked for this job.</p>
                    <?php else: ?>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <?php foreach ($responses as $q => $a): ?>
                                <div>
                                    <div class="response-q"><?php echo htmlspecialchars($q); ?></div>
                                    <div class="response-a"><?php echo htmlspecialchars($a); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <h3>AI Generated Cover Letter</h3>
                <div style="background: rgba(0, 0, 0, 0.2); padding: 30px; border-radius: 16px; font-style: italic; white-space: pre-line; border: 1px solid var(--border-color); line-height: 1.8; color: #cbd5e1;">
                    <?php echo htmlspecialchars($app['cover_letter']); ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
