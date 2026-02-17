<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/config.php';
require_once '../includes/db.php';

$plan = $_GET['plan'] ?? 'monthly';
$price = ($plan == 'yearly') ? 15 : 5;
$plan_name = ucfirst($plan) . " Pro Plan";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    // In a real app, you would verify the bank slip/transaction ID here
    $duration = ($plan == 'yearly') ? '+1 year' : '+1 month';
    $new_expiry = date('Y-m-d H:i:s', strtotime($duration));
    
    $stmt = $pdo->prepare("UPDATE users SET account_type = 'pro', subscription_end = ? WHERE id = ?");
    if ($stmt->execute([$new_expiry, $_SESSION['user_id']])) {
        $payment_success = true;
        // Generate a fake transaction ID
        $trans_id = "TRX-" . rand(10000000, 99999999);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css?v=71.0">
 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-glow: #00f2ff;
            --mist-bg: #05080a;
            --card-glass: rgba(0, 242, 255, 0.03);
            --border-glass: rgba(0, 242, 255, 0.2);
        }

        body {
            background: var(--mist-bg);
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 242, 255, 0.1) 0%, #05080a 100%);
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
            overflow-x: hidden;
        }

        .checkout-page-wrapper {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 60px 20px;
        }

        @keyframes mistMove {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(5%, 5%) scale(1.1); }
        }

        .payment-card {
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            width: 100%;
            max-width: 420px;
            border-radius: 30px;
            border: 1px solid var(--border-glass);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            padding: 40px;
            text-align: center;
            position: relative;
            animation: cardEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: perspective(1000px) rotateX(-10deg) translateY(50px); }
            to { opacity: 1; transform: perspective(1000px) rotateX(0) translateY(0); }
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(0, 242, 255, 0.1);
            border: 1px solid rgba(0, 242, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.2rem;
            color: var(--green-glow);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.2);
        }

        .plan-info {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .plan-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--green-glow);
            margin-top: 10px;
            text-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #6B7280;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 242, 255, 0.2);
            border-radius: 12px;
            font-size: 1rem;
            color: white;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--green-glow);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.2);
            transform: scale(1.01);
        }

        .form-group select {
            width: 100%;
            padding: 14px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(0, 242, 255, 0.2);
            border-radius: 12px;
            font-size: 1rem;
            color: white;
            transition: all 0.3s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300f2ff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
        }

        .form-group select:focus {
            outline: none;
            border-color: var(--green-glow);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.2);
            transform: scale(1.01);
        }

        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-pay {
            background: linear-gradient(135deg, var(--green-glow) 0%, var(--secondary-color) 100%);
            color: #05080a;
            border: none;
            width: 100%;
            padding: 18px;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(0, 242, 255, 0.3);
        }

        .btn-pay:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 242, 255, 0.4);
        }

        .btn-pay:active {
            transform: scale(0.98);
        }

        /* Success screen styles */
        .success-checkmark {
            width: 100px;
            height: 100px;
            background: var(--green-glow);
            color: #05080a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 30px;
            box-shadow: 0 10px 20px rgba(0, 242, 255, 0.3);
        }

        .success-amount {
            font-size: 3rem;
            font-weight: 800;
            color: var(--green-glow);
            margin-bottom: 20px;
        }

        .success-details {
            border-top: 1px dashed #E5E7EB;
            padding-top: 20px;
            text-align: left;
            margin-bottom: 30px;
        }

        .btn-done {
            background: var(--green-glow);
            color: #05080a;
            border: none;
            width: 100%;
            padding: 18px;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .back-link {
            color: #6B7280;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <div class="checkout-page-wrapper">
        <?php if (isset($payment_success)): ?>
            <!-- Success Screen -->
            <div class="payment-card">
                <h2 style="margin-bottom: 30px; font-weight: 700;">SUCCESSFUL TRANSFER!</h2>
                
                <div class="success-checkmark">
                    ✓
                </div>

                <div class="success-amount">
                    $<?php echo number_format($price, 2); ?>
                </div>

                <div class="success-details">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            👤
                        </div>
                        <div>
                            <p style="font-size: 0.7rem; color: #6B7280; text-transform: uppercase; margin: 0;">Transfer to</p>
                            <p style="font-weight: 700; margin: 0;"><?php echo $_SESSION['user_name']; ?></p>
                        </div>
                    </div>
                    
                    <p style="font-size: 0.7rem; color: #6B7280; text-align: center; margin: 0;">Transaction ID</p>
                    <p style="font-weight: 600; text-align: center; margin: 5px 0 0;"><?php echo $trans_id; ?></p>
                </div>

                <a href="dashboard.php" class="btn-done">Done</a>
                
                <div style="display: flex; justify-content: space-around; margin-top: 20px; font-size: 0.8rem; color: #6B7280;">
                    <span style="cursor: pointer;">Share Reciept</span>
                    <span>|</span>
                    <span style="cursor: pointer;">Image reciept</span>
                </div>
            </div>
        <?php else: ?>
            <!-- Selection/Payment Screen -->
            <div class="payment-card">
                <div class="header-icon">🏦</div>
                <h2 style="font-weight: 800; margin-bottom: 10px;">Secure Payment</h2>
                <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 30px;">Choose your payment method and upgrade to Pro.</p>

                <div class="plan-info">
                    <div class="plan-row">
                        <span>Plan</span>
                        <span style="font-weight: 700;"><?php echo $plan_name; ?></span>
                    </div>
                    <div class="plan-row">
                        <span>Duration</span>
                        <span><?php echo ($plan == 'yearly') ? '1 Year' : '1 Month'; ?></span>
                    </div>
                    <div class="plan-price">$<?php echo number_format($price, 2); ?></div>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label>Card Holder Name</label>
                        <input type="text" name="card_holder" placeholder="e.g. ROSE" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>

                    <div class="card-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                        <div class="form-group">
                            <label>Expiry Month</label>
                            <select name="expiry_month" required>
                                <option value="" disabled selected>MM</option>
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Expiry Year</label>
                            <select name="expiry_year" required>
                                <option value="" disabled selected>YY</option>
                                <?php 
                                $current_year = date('y');
                                for($i=0; $i<=10; $i++): 
                                    $year = $current_year + $i;
                                ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>CVV / PIN</label>
                            <input type="password" name="cvv" placeholder="***" maxlength="3" required>
                        </div>
                    </div>

                    <button type="submit" name="confirm_payment" class="btn-pay">Upgrade Now</button>
                </form>

                <a href="pricing.php" class="back-link">← Choose another plan</a>
                
                <div style="margin-top: 30px; display: flex; justify-content: center; gap: 15px; opacity: 0.5;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" height="15" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="20" alt="Mastercard">
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
