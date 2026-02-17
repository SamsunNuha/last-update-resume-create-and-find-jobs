<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_job'])) {
    $title = $_POST['title'];
    $company = $_POST['company'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];
    $category_id = $_POST['category_id'];
    $reqs = $_POST['requirements'];
    $desc = $_POST['description'];
    $form_fields = json_encode($_POST['form_fields'] ?? []);

    $stmt = $pdo->prepare("INSERT INTO jobs (title, company, location, salary_range, category_id, requirements, description, application_form) VALUES (?,?,?,?,?,?,?,?)");
    if ($stmt->execute([$title, $company, $location, $salary, $category_id, $reqs, $desc, $form_fields])) {
        $success = "Job posted successfully!";
    }
}

$jobs = $pdo->query("SELECT j.*, c.name as category_name FROM jobs j LEFT JOIN job_categories c ON j.category_id = c.id ORDER BY j.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM job_categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - <?php echo SITE_NAME; ?></title>
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
        .nav-links a { color: white; opacity: 0.7; text-decoration: none; padding: 10px 20px; font-weight: 600; }
        .nav-links a.active { opacity: 1; color: var(--primary-color); background: rgba(255,255,255,0.1); border-radius: 12px; }
        
        .dashboard-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 24px;
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .action-btns { display: flex; gap: 10px; }
        .btn-edit { background: var(--primary-color); color: #06385a; padding: 6px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; text-decoration: none; }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 6px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; text-decoration: none; border: 1px solid rgba(239, 68, 68, 0.2); }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: transparent; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { color: var(--primary-color); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; font-weight: 700; }
        tr:hover td { background: rgba(0, 242, 255, 0.05); }

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

    <div class="admin-nav">
        <div class="container">
            <a href="dashboard.php" class="logo">Admin<span class="cyan">Panel</span></a>
            <div class="nav-links">
                <a href="dashboard.php">Stats</a>
                <a href="manage_jobs.php" class="active">Jobs</a>
                <a href="../logout.php" style="color:#FCA5A5">Logout</a>
            </div>
        </div>
    </div>
    <main class="container" style="padding-top: 50px;">
        <h1>Manage Jobs</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h3>Post New Job</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Job Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" name="company" required>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" required>
                    </div>
                    <div class="form-group">
                        <label>Salary Range</label>
                        <input type="text" name="salary">
                    </div>
                    <div class="form-group">
                        <label>Job Category</label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Requirements (Skills - comma separated)</label>
                        <input type="text" name="requirements" required placeholder="PHP, JavaScript, MySQL">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>

                    <div style="background: #f3f4f6; padding: 20px; border-radius: 12px; margin-bottom: 25px;">
                        <h4 style="margin-top:0; color:#1f2937">📋 Application Questions (Optional)</h4>
                        <p style="font-size:0.85rem; color:#64748b; margin-bottom:15px">Ask specific details like "Expected Salary" or "Notice Period".</p>
                        <div id="form-fields-container">
                            <!-- Fields added here -->
                        </div>
                        <button type="button" class="btn btn-secondary" style="width:auto; font-size:0.85rem" onclick="addField()">+ Add Question</button>
                    </div>

                    <button type="submit" name="add_job" class="btn btn-primary">Post Job</button>
                </form>
            </div>

            <script>
                function addField() {
                    const container = document.getElementById('form-fields-container');
                    const index = container.children.length;
                    const div = document.createElement('div');
                    div.style.display = 'flex';
                    div.style.gap = '10px';
                    div.style.marginBottom = '10px';
                    div.innerHTML = `
                        <input type="text" name="form_fields[${index}]" placeholder="Question (e.g. Expected Salary?)" required style="flex:1">
                        <button type="button" onclick="this.parentElement.remove()" style="background:#fee2e2; color:#ef4444; border:none; padding:10px; border-radius:8px; cursor:pointer">X</button>
                    `;
                    container.appendChild(div);
                }
            </script>

            <div class="dashboard-card" style="grid-column: span 2;">
                <h3>Existing Jobs</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Job</th>
                            <th>Company</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                <td><?php echo htmlspecialchars($job['company']); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn-edit">Edit</a>
                                        <a href="delete_job.php?id=<?php echo $job['id']; ?>" class="btn-delete" onclick="return confirm('Delete this job?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
