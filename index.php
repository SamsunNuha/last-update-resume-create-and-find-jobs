<?php
session_start();
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Your Career Starts Here</title>
    <link rel="stylesheet" href="assets/css/style.css?v=71.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/images/logo1.png">
</head>
<body class="home-page">
    <div class="mist-container">
        <div class="mist-blob"></div>
        <div class="mist-blob"></div>
        <div class="mist-blob"></div>
    </div>
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">
                    <img src="assets/images/logo_blue.png" alt="LankaResumey" class="logo-img">
                    <div class="logo-text">
                        <span class="logo-name">Lanka<span class="cyan">Resumey</span></span>
                    </div>
                </a>
                <div class="nav-links">
                    <a href="index.php" class="active">🏠 Home</a>
                    <a href="#features">Features</a>
                    <a href="user/pricing.php">Pricing</a>
                </div>
                <div class="nav-auth">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="user/dashboard.php" class="nav-btn btn-nav-register" style="min-width:auto; height:42px; padding:0 24px;">My Dashboard</a>
                    <?php else: ?>
                        <a href="admin/login.php" class="nav-btn" style="color: var(--primary-color); border-color: rgba(0, 242, 255, 0.3); margin-right: 10px;">Admin</a>
                        <a href="login.php" class="nav-btn btn-nav-login">Login</a>
                        <a href="register.php" class="nav-btn btn-nav-register">Get Started</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="hero">

        <h1>Build Your Resume <span style="display: block;">Find Your Dream Job</span></h1>

        <p>Create professional A4 resumes, let AI scan your skills, and get matched with perfect job opportunities. Apply with one click.</p>

        <div class="hero-btns">
            <a href="register.php" class="btn-primary">
                Create Your Resume <span>&rarr;</span>
            </a>
            <a href="user/resume_preview.php?demo=true" class="btn-secondary">
                <span>👁️</span> Try Demo
            </a>
            <a href="index.php" class="btn-secondary">
                <span style="color: #ffcc33;">💼</span> Browse Jobs
            </a>
        </div>

        <div class="hero-features">
            <div class="item"><span class="check">✓</span> Free to start</div>
            <div class="item"><span class="check">✓</span> PDF Export</div>
            <div class="item"><span class="check">✓</span> AI Matching</div>
        </div>
    </main>

    <section id="features" class="container">
        <div class="features-grid">
            <div class="feature-block">
                <div class="feature-icon-box">📄</div>
                <h3>Smart Resume Builder</h3>
                <p>Multi-step form with live preview and professionally designed A4 templates.</p>
            </div>
            <div class="feature-block">
                <div class="feature-icon-box">🧠</div>
                <h3>Intelligent Matching</h3>
                <p>Our algorithm scans your resume and matches you with the best job opportunities.</p>
            </div>
            <div class="feature-block">
                <div class="feature-icon-box">🖱️</div>
                <h3>One-Click Apply</h3>
                <p>Apply to multiple jobs instantly using your generated resume.</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="container">
        <div class="section-title">
            <h2>Fair & Transparent <span style="color: var(--primary-color);">Pricing</span></h2>
            <p style="margin-top: 20px; opacity: 0.7;">Choose the plan that fits your career stage. Upgrade or downgrade at any time.</p>
        </div>

        <div class="pricing-grid">
            <!-- Free Plan -->
            <div class="pricing-card free">
                <h3>Lifetime Free</h3>
                <div class="price">$0<span>/forever</span></div>
                <ul>
                    <li>✓ 1 Active Resume</li>
                    <li>✓ AI Generation (Limited)</li>
                    <li>✓ Standard PDF Export</li>
                    <li>✓ Public Profile Link</li>
                </ul>
                <a href="register.php" class="btn-pricing" style="background: rgba(255,255,255,0.1); color: #94a3b8; pointer-events: none; border-color: rgba(255,255,255,0.05);">Active Plan</a>
            </div>

            <!-- Popular Plan -->
            <div class="pricing-card popular">
                <div class="popular-badge">POPULAR</div>
                <h3>Pro Monthly</h3>
                <div class="price">$5<span>/month</span></div>
                <ul>
                    <li>✓ Unlimited Resumes</li>
                    <li>✓ All 6 Premium Templates</li>
                    <li>✓ Professional Bank Details</li>
                    <li>✓ Profile Photo Support</li>
                    <li>✓ ATS Gap Analysis</li>
                </ul>
                <a href="register.php" class="btn-pricing cyan">Upgrade Now &rarr;</a>
            </div>

            <!-- Pro Yearly -->
            <div class="pricing-card pro-yearly">
                <h3>Pro Yearly</h3>
                <div class="price">$45<span>/year</span></div>
                <ul>
                    <li>✓ All Pro Monthly Features</li>
                    <li>✓ <strong>76% Savings</strong> Yearly</li>
                    <li>✓ Priority Support</li>
                    <li>✓ Exclusive Future Updates</li>
                </ul>
                <a href="register.php" class="btn-pricing gold">Upgrade & Save &rarr;</a>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <p>&copy; 2026 LankaResumey. All rights reserved. Built by Sri Lankan Professionals - Privacy Policy | Terms of Service | <a href="admin/login.php" style="color: #64748b; text-decoration: none;">Admin Login</a></p>
    </footer>
</body>
</html>
