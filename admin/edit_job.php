<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) die("Job not found.");

$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_job'])) {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $category_id = $_POST['category_id'];
    $reqs = $_POST['requirements'];
    $desc = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE jobs SET title=?, company=?, location=?, salary_range=?, category_id=?, requirements=?, description=? WHERE id=?");
    if ($stmt->execute([$title, $company, $location, $salary, $category_id, $reqs, $desc, $id])) {
        $success = "Job updated successfully!";
    }
}

$categories = $pdo->query("SELECT * FROM job_categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - <?php echo htmlspecialchars($job['title']); ?></title>
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
        .nav-links a { color: white; opacity: 0.7; text-decoration: none; padding: 10px 20px; font-weight: 600; border-radius: 12px; }
        .nav-links a:hover { opacity: 1; background: rgba(255,255,255,0.1); color: var(--primary-color); }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); font-weight: 700; text-decoration: none; margin-bottom: 25px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .dashboard-card { 
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            padding: 35px; 
            border-radius: 28px; 
            border: 1px solid var(--border-color);
        }
        .form-group label { color: var(--text-color); opacity: 0.9; font-weight: 650; }
        .form-group input, .form-group textarea, .form-group select { 
            background: rgba(0, 0, 0, 0.3); 
            border: 1px solid var(--border-color); 
            color: white; 
            padding: 14px;
        }
        .form-group input:focus { border-color: var(--primary-color); box-shadow: 0 0 15px rgba(0, 242, 255, 0.2); }
    </style>
</head>
<body class="home-page">

    <div class="admin-nav" style="margin-bottom: 50px;">
        <div class="container">
            <a href="dashboard.php" class="logo">Admin<span class="cyan">Panel</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Stats</a>
                <a href="manage_jobs.php" class="active">Jobs</a>
                <a href="../logout.php" style="color:#FCA5A5">Logout</a>
            </div>
        </div>
    </div>
            <div class="nav-links">
                <a href="dashboard.php" style="color:white; opacity: 0.7; text-decoration:none; padding: 8px 16px;">Stats</a>
                <a href="manage_jobs.php" style="color:white; font-weight: 700; text-decoration:none; padding: 8px 16px;">Jobs</a>
                <a href="../logout.php" style="color:#FCA5A5; text-decoration:none; padding: 8px 16px;">Logout</a>
            </div>
        </div>
    </div>

    <main class="container">
        <a href="manage_jobs.php" class="back-link">← Back to Manage Jobs</a>
        <h1>Edit Job Posting</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="dashboard-card" style="max-width: 600px; margin: 20px auto;">
            <form method="POST">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required>
                </div>
                <!-- ... other fields ... -->
                <div class="form-group">
                    <label>Company</label>
                    <input type="text" name="company" value="<?php echo htmlspecialchars($job['company']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Salary Range</label>
                    <input type="text" name="salary" value="<?php echo htmlspecialchars($job['salary_range']); ?>">
                </div>
                <div class="form-group">
                    <label>Job Category</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $job['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Requirements (Skills)</label>
                    <input type="text" name="requirements" value="<?php echo htmlspecialchars($job['requirements']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($job['description']); ?></textarea>
                </div>
                <button type="submit" name="update_job" class="btn btn-primary">Update Job</button>
            </form>
        </div>
    </main>

</body>
</html>
