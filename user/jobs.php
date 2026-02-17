<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

// Get user skills
$stmt = $pdo->prepare("SELECT skills FROM resumes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$resume = $stmt->fetch();
$user_skills = $resume ? array_map('trim', explode(',', strtolower($resume['skills']))) : [];

// Search logic
$search = $_GET['q'] ?? '';
$location = $_GET['l'] ?? '';
$category_id = $_GET['c'] ?? '';

$query = "SELECT * FROM jobs WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (title LIKE ? OR requirements LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if ($category_id) {
    $query .= " AND category_id = ?";
    $params[] = $category_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM job_categories ORDER BY name ASC")->fetchAll();

function calculateMatch($user_skills, $job_requirements) {
    if (empty($user_skills)) return 0;
    $reqs = array_map('trim', explode(',', strtolower($job_requirements)));
    $matches = array_intersect($user_skills, $reqs);
    return round((count($matches) / count(array_unique($reqs))) * 100);
}

function getMatchLabel($percent) {
    if ($percent >= 80) return "Best Match";
    if ($percent >= 50) return "Good Match";
    return "Low Match";
}

function getMatchClass($percent) {
    if ($percent >= 80) return "match-best";
    if ($percent >= 50) return "match-good";
    return "match-low";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Jobs - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=71.0">
   
    <style>
        .search-bar { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 25px; 
            border-radius: 16px; 
            display: flex; 
            gap: 15px; 
            border: 1px solid var(--border-color);
            margin-bottom: 30px; 
        }
        .search-bar input { 
            flex: 1; 
            padding: 14px; 
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            color: white;
            transition: all 0.3s;
        }
        .search-bar input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.15);
        }
        
        .job-list { display: grid; gap: 20px; }
        .job-card { 
            background: var(--card-bg); 
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 25px; 
            border-radius: 20px; 
            border: 1px solid var(--border-color); 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            transition: all 0.3s;
        }
        .job-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 242, 255, 0.1);
        }
        .job-main h2 { color: var(--primary-color); font-size: 1.3rem; margin-bottom: 5px; font-weight: 700; }
        .job-meta { color: var(--text-color); opacity: 0.8; font-size: 0.95rem; margin-bottom: 15px; }
        .job-reqs { display: flex; gap: 10px; flex-wrap: wrap; }
        
        .match-indicator { text-align: right; min-width: 120px; }
        .match-score { font-size: 1.8rem; font-weight: 800; color: var(--primary-color); margin-bottom: 5px; text-shadow: 0 0 10px rgba(0, 242, 255, 0.3); }
        .match-badge-small { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .match-best { background: rgba(0, 242, 255, 0.2); color: var(--primary-color); border: 1px solid var(--primary-color); }
        .match-good { background: rgba(0, 162, 255, 0.2); color: var(--secondary-color); border: 1px solid var(--secondary-color); }
        .match-low { background: rgba(100, 116, 139, 0.1); color: var(--text-muted); border: 1px solid rgba(100, 116, 139, 0.3); }

        .jobs-container { display: flex; flex-direction: column; gap: 30px; margin-top: 30px; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1>Find Your Dream Job</h1>
                <p>Smart recommendations based on your resume</p>
            </div>
            <button class="btn btn-secondary" onclick="createAlert()">🔔 Create Job Alert</button>
        </div>

        <form class="search-bar" method="GET">
            <input type="text" name="q" placeholder="Job title or skill..." value="<?php echo htmlspecialchars($search); ?>">
            <input type="text" name="l" placeholder="Location..." value="<?php echo htmlspecialchars($location); ?>">
            <div style="display: flex; align-items: center; gap: 15px; margin-left: 10px; background: rgba(255,255,255,0.02); padding: 8px 15px; border-radius: 10px; border: 1px solid var(--border-color);">
                <input type="checkbox" name="smart_filter" value="1" id="smart_filter" <?php echo isset($_GET['smart_filter']) ? 'checked' : ''; ?> style="width: 20px; height: 20px; accent-color: var(--primary-color);">
                <label for="smart_filter" style="white-space: nowrap; font-size: 0.95rem; font-weight: 600; color: var(--text-color); cursor: pointer;">Match My Skills</label>
            </div>
            <button type="submit" class="btn btn-primary" style="width: auto;">Search</button>
        </form>

        <div class="jobs-container">
            <div class="job-list">
                <?php 
                // Calculate match scores for all jobs
                foreach ($jobs as &$job) {
                    $job['match_score'] = calculateMatch($user_skills, $job['requirements']);
                }
                unset($job); // Break reference

                // Sort by match score descending
                usort($jobs, function($a, $b) {
                    return $b['match_score'] <=> $a['match_score'];
                });

                // Filter logic
                if (isset($_GET['smart_filter'])) {
                    $jobs = array_filter($jobs, function($j) {
                        return $j['match_score'] >= 40; // Show only decent matches
                    });
                }

                if (empty($jobs)): 
                ?>
                    <div class="job-card" style="justify-content: center; padding: 50px;">
                        <p style="color: #666;">No jobs found matching your criteria.</p>
                    </div>
                <?php endif; ?>
                
                <?php foreach ($jobs as $job): 
                    $match = $job['match_score'];
                ?>
                    <div class="job-card">
                        <div class="job-main">
                            <h2><?php echo htmlspecialchars($job['title']); ?></h2>
                            <div class="job-meta">
                                <strong><?php echo htmlspecialchars($job['company']); ?></strong> • 
                                <?php echo htmlspecialchars($job['location']); ?> • 
                                <?php echo htmlspecialchars($job['salary_range']); ?>
                            </div>
                            <div class="job-reqs">
                                <?php foreach (explode(',', $job['requirements']) as $req): ?>
                                    <span class="skill-tag"><?php echo trim($req); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="match-indicator">
                            <div class="match-score"><?php echo $match; ?>%</div>
                            <div class="match-badge-small <?php echo getMatchClass($match); ?>">
                                <?php echo getMatchLabel($match); ?>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button class="btn btn-primary" onclick="apply(<?php echo $job['id']; ?>)">Apply</button>
                                <button class="btn btn-secondary" onclick="toggleFavorite(<?php echo $job['id']; ?>, this)">⭐</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Application Modal -->
    <div id="applyModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:white; padding:30px; border-radius:12px; max-width:500px; width:90%; position:relative;">
            <button onclick="closeModal()" style="position:absolute; top:15px; right:15px; border:none; background:none; font-size:1.5rem; cursor:pointer">×</button>
            <h3 id="modalTitle">Complete Application</h3>
            <p id="modalSub" style="color:#64748b; font-size:0.9rem; margin-bottom:20px"></p>
            
            <form id="applyForm">
                <input type="hidden" name="job_id" id="modalJobId">
                <div id="dynamicFields"></div>
                
                <div style="margin-top:25px; display:flex; gap:10px">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1">Submit Application</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const jobsData = <?php echo json_encode(array_values($jobs)); ?>;

        function apply(jobId) {
            const job = jobsData.find(j => j.id == jobId);
            const formFields = JSON.parse(job.application_form || '[]');
            
            // One-Click Apply logic
            if (formFields.length === 0) {
                if (confirm(`Apply to ${job.company} for ${job.title}?\n\nWe will attach your LankaResumey automatically.`)) {
                    submitApplication(jobId, {});
                }
                return;
            }

            // Show Modal for extra fields
            document.getElementById('modalJobId').value = jobId;
            document.getElementById('modalTitle').innerText = 'Apply for ' + job.title;
            document.getElementById('modalSub').innerText = 'Please provide these additional details required by ' + job.company;
            
            const fieldsContainer = document.getElementById('dynamicFields');
            fieldsContainer.innerHTML = '';
            
            formFields.forEach((q, i) => {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.style.marginBottom = '15px';
                div.innerHTML = `
                    <label style="display:block; margin-bottom:8px; font-weight:600">${q}</label>
                    <input type="text" name="responses[${q}]" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px">
                `;
                fieldsContainer.appendChild(div);
            });
            
            document.getElementById('applyModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('applyModal').style.display = 'none';
        }

        function createAlert() {
            const q = document.querySelector('input[name="q"]').value;
            const l = document.querySelector('input[name="l"]').value;
            
            if(!q && !l) {
                alert("Please enter a job title or location in the search bar first to set an alert.");
                return;
            }

            const formData = new FormData();
            formData.append('keywords', q);
            formData.append('location', l);

            fetch('../api/create_alert.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => alert(data.message));
        }

        document.getElementById('applyForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const jobId = formData.get('job_id');
            submitApplication(jobId, formData);
        }

        function submitApplication(jobId, formData) {
            let body;
            if (formData instanceof FormData) {
                body = formData;
            } else {
                body = new FormData();
                body.append('job_id', jobId);
            }

            fetch('../api/apply.php', {
                method: 'POST',
                body: body
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    closeModal();
                }
            });
        }

        function toggleFavorite(jobId, btn) {
            const body = new FormData();
            body.append('job_id', jobId);
            fetch('../api/favorite.php', {
                method: 'POST',
                body: body
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    btn.classList.toggle('active');
                    alert(data.message);
                }
            });
        }
    </script>

</body>
</html>
