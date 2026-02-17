<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Verify user exists in database before proceeding
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        // User doesn't exist - clear session and force re-login
        session_destroy();
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please logout and login again.']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection error']);
    exit;
}


// Check for empty POST data which could indicate post_max_size exceeded
if (empty($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data too large. Please reduce image size or content. (Max: ' . ini_get('post_max_size') . ')']);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = $_POST;

// Extract standard fields
$full_name = $data['full_name'] ?? '';
$email = $data['email'] ?? '';
$phone = $data['phone'] ?? '';
$website = $data['website'] ?? '';
$portfolio = $data['portfolio'] ?? '';
$address = $data['address'] ?? '';
$summary = $data['summary'] ?? '';
$education = json_encode($data['edu'] ?? []);
$experience = json_encode($data['exp'] ?? []);
$skills = $data['skills'] ?? '';
$projects = json_encode($data['proj'] ?? []);
$certifications = json_encode($data['cert'] ?? []);
$template_id = $data['template_id'] ?? 1;
$bank_name = $data['bank_name'] ?? '';
$branch_name = $data['branch_name'] ?? '';
$acc_no = $data['acc_no'] ?? '';
$acc_name = $data['acc_name'] ?? '';
$extra_details = json_encode($data['extra'] ?? []);

try {
    // Check if resume exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM resumes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        $sql = "UPDATE resumes SET full_name=?, email=?, phone=?, website=?, portfolio=?, address=?, summary=?, education=?, experience=?, skills=?, projects=?, certifications=?, template_id=?, bank_name=?, branch_name=?, acc_no=?, acc_name=?, extra_details=?, updated_at=NOW() WHERE user_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$full_name, $email, $phone, $website, $portfolio, $address, $summary, $education, $experience, $skills, $projects, $certifications, $template_id, $bank_name, $branch_name, $acc_no, $acc_name, $extra_details, $user_id]);
    } else {
        // Allow draft creation even without full name to prevent data loss
        $sql = "INSERT INTO resumes (user_id, full_name, email, phone, website, portfolio, address, summary, education, experience, skills, projects, certifications, template_id, bank_name, branch_name, acc_no, acc_name, extra_details) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $full_name, $email, $phone, $website, $portfolio, $address, $summary, $education, $experience, $skills, $projects, $certifications, $template_id, $bank_name, $branch_name, $acc_no, $acc_name, $extra_details]);
    }

    $response = ['status' => 'success', 'timestamp' => date('H:i:s')];
    $json = json_encode($response);
    
    if ($json === false) {
        throw new Exception("JSON encoding failed: " . json_last_error_msg());
    }
    
    echo $json;

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
