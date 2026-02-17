<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Job Notification Logic
$notif_jobs = [];
$notif_count = 0;
$sys_notifs = [];

if (isset($_SESSION['user_id'])) {
    // Get user skills AND extra details (for job title)
    $stmt = $pdo->prepare("SELECT skills, extra_details FROM resumes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $resume_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resume_data) {
        $skills_raw = $resume_data['skills'] ?? '';
        $extra = json_decode($resume_data['extra_details'] ?? '{}', true);
        $target_title = $extra['job_title'] ?? '';
        
        $conditions = [];
        $params = [];

        // 1. Match by Target Job Title (High Priority)
        if (!empty($target_title)) {
            $conditions[] = "title LIKE :target_title";
            $params[':target_title'] = '%' . $target_title . '%';
        }

        // 2. Match by Skills
        if ($skills_raw) {
            $user_skills = array_map('trim', explode(',', strtolower($skills_raw)));
            $user_skills = array_filter($user_skills);
            
            foreach ($user_skills as $i => $skill) {
                if(empty($skill)) continue;
                $key = ":skill_$i";
                $conditions[] = "LOWER(requirements) LIKE $key";
                $params[$key] = '%' . $skill . '%';
            }
        }

        if (!empty($conditions)) {
            // Fetch Top 3 latest matches
            $sql = "SELECT id, title, company, salary_range, created_at FROM jobs WHERE " . implode(" OR ", $conditions) . " ORDER BY created_at DESC LIMIT 3";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) { $stmt->bindValue($key, $val); }
            $stmt->execute();
            $notif_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Fetch System Notifications (Applications, etc.)
    $stmt_sys = $pdo->prepare("SELECT * FROM user_notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
    $stmt_sys->execute([$_SESSION['user_id']]);
    $sys_notifs = $stmt_sys->fetchAll(PDO::FETCH_ASSOC);

    $notif_count = count($notif_jobs) + count($sys_notifs);
}
?>
<div class="mist-container">
    <div class="mist-blob"></div>
    <div class="mist-blob"></div>
    <div class="mist-blob"></div>
</div>
<header>
    <div class="container">
        <nav>
            <a href="../index.php" class="logo">
                <img src="../assets/images/logo_blue.png" alt="LankaResumey" class="logo-img">
                <div class="logo-text">
                    <span class="logo-name">Lanka<span class="cyan">Resumey</span></span>
                </div>
            </a>
            <div class="nav-links">
                <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="resume_builder.php" class="<?php echo $current_page == 'resume_builder.php' ? 'active' : ''; ?>">Build Resume</a>
                <a href="jobs.php" class="<?php echo $current_page == 'jobs.php' ? 'active' : ''; ?>">Find Jobs</a>
                <a href="applications.php" class="<?php echo $current_page == 'applications.php' ? 'active' : ''; ?>">My Applications</a>
            </div>
            <div class="nav-auth">
                <div class="notif-wrapper" style="position: relative; margin-right: 15px;">
                    <a href="javascript:void(0)" class="notif-bell btn-icon" onclick="toggleNotif(event)" title="Matching Job Notifications">
                        🔔
                        <?php if ($notif_count > 0): ?>
                            <span class="notif-badge"><?php echo $notif_count; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if ($notif_count > 0): ?>
                    <div id="notif-list" style="display:none; position:absolute; top:45px; right:0; width:320px; background:rgba(5, 8, 10, 0.95); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid rgba(0, 242, 255, 0.2); box-shadow:0 10px 40px rgba(0,0,0,0.6); border-radius:12px; z-index:1000; overflow:hidden;">
                        <div style="padding:15px; border-bottom:1px solid rgba(255,255,255,0.05); background:rgba(255,255,255,0.02); display:flex; justify-content:space-between; align-items:center">
                            <h4 style="margin:0; font-size:0.95rem; color:#e2f9ff; font-weight:700">✨ Top Matches</h4>
                            <span style="font-size:0.75rem; color:var(--primary-color); background:rgba(0, 242, 255, 0.1); padding:2px 6px; border-radius:4px"><?php echo $notif_count; ?> New</span>
                        </div>
                        <div style="max-height:350px; overflow-y:auto;">
                            <!-- System Notifications -->
                            <?php foreach($sys_notifs as $sn): ?>
                                <div class="notif-item" style="display:block; padding:15px; border-bottom:1px solid rgba(255,255,255,0.05); background: rgba(0, 242, 255, 0.03);">
                                    <div style="color:var(--primary-color); font-size:0.85rem; line-height:1.4; font-weight:500;">
                                        ✅ <?php echo htmlspecialchars($sn['message']); ?>
                                    </div>
                                    <div style="color:var(--text-muted); font-size:0.75rem; margin-top:5px;">
                                        🕒 <?php echo date('M d, H:i', strtotime($sn['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <!-- Job Matches -->
                            <?php if(!empty($notif_jobs)): ?>
                                <div style="padding:10px 15px; background:rgba(255,255,255,0.03); font-size:0.75rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px;">Recommended Jobs</div>
                                <?php foreach($notif_jobs as $job): ?>
                                    <a href="jobs.php?id=<?php echo $job['id']; ?>" class="notif-item" style="display:block; padding:15px; border-bottom:1px solid rgba(255,255,255,0.05); text-decoration:none; transition:background 0.2s;">
                                        <div style="display:flex; justify-content:space-between; margin-bottom:4px">
                                            <div style="color:#e2f9ff; font-weight:600; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;"><?php echo htmlspecialchars($job['title']); ?></div>
                                            <div style="color:var(--primary-color); font-size:0.8rem; font-weight:600;"><?php echo htmlspecialchars($job['salary_range']); ?></div>
                                        </div>
                                        <div style="color:#94a3b8; font-size:0.85rem; display:flex; align-items:center; gap:5px">
                                            <span>🏢 <?php echo htmlspecialchars($job['company']); ?></span>
                                            <span style="color:rgba(255,255,255,0.1)">•</span>
                                            <span style="font-size:0.75rem">Apply Now 🚀</span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <a href="jobs.php" style="display:block; padding:12px; text-align:center; font-size:0.85rem; color:var(--primary-color); font-weight:600; text-decoration:none; background:rgba(255,255,255,0.02); border-top:1px solid rgba(255,255,255,0.05);">See All Jobs →</a>
                    </div>
                    <?php endif; ?>
                </div>

                <style>
                    .btn-icon {
                        background: rgba(0, 242, 255, 0.1); 
                        color: var(--primary-color);
                        width: 40px;
                        height: 40px;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        border-radius: 50%;
                        font-size: 1.2rem;
                        text-decoration: none;
                        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        border: 1px solid rgba(0, 242, 255, 0.2);
                        cursor: pointer;
                    }
                    .btn-icon:hover {
                        background: rgba(0, 242, 255, 0.2);
                        color: white;
                        transform: translateY(-3px) scale(1.1);
                        box-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
                        border-color: rgba(0, 242, 255, 0.4);
                    }
                    .notif-badge {
                        position: absolute; 
                        top: -2px; 
                        right: -2px; 
                        background: #ef4444; 
                        color: white; 
                        font-size: 0.65rem; 
                        padding: 2px 5px; 
                        border-radius: 50%; 
                        font-weight: bold; 
                        border: 2px solid white;
                        line-height: 1;
                        min-width: 16px;
                    }
                    .notif-item:hover { background: rgba(0, 242, 255, 0.05); }
                </style>

                <script>
                function toggleNotif(e) {
                    e.preventDefault();
                    // Just toggle matching section logic
                    const list = document.getElementById('notif-list');
                    if(list) list.style.display = list.style.display === 'none' ? 'block' : 'none';
                }
                
                // Close when clicking outside
                document.addEventListener('click', function(e) {
                    const wrapper = document.querySelector('.notif-wrapper');
                    const list = document.getElementById('notif-list');
                    if(wrapper && list && !wrapper.contains(e.target)) {
                        list.style.display = 'none';
                    }
                });
                </script>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pricing.php" class="nav-btn btn-nav-pro">Upgrade Pro 🚀</a>
                    <span class="user-name">Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                    <a href="../logout.php" class="nav-btn btn-nav-logout" style="margin-left: 10px;">Logout</a>
                <?php else: ?>
                    <a href="../admin/login.php" class="nav-btn" style="color: var(--primary-color); border-color: rgba(0, 242, 255, 0.3); margin-right: 10px;">Admin</a>
                    <a href="../login.php" class="nav-btn btn-nav-login">Login</a>
                    <a href="../register.php" class="nav-btn btn-nav-register">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
    
</header>
