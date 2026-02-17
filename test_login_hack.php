<?php
// Simulate a login POST request to admin/login.php
$url = 'http://localhost/Resume_make catch Job/admin/login.php';
$data = array('login' => '1', 'username' => 'admin@gmail.com', 'password' => 'sams');

// 1. Login
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/Resume_make%20catch%20Job/admin/login.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response Code: " . $httpCode . "\n";

// 2. Dashboard Access
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/Resume_make%20catch%20Job/admin/dashboard.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Dashboard Response Code: " . $httpCode . "\n";
echo "Dashboard Response Headers:\n" . substr($response, 0, 500) . "\n...\n";
?>
