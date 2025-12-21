<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");


$whitelist = array('127.0.0.1', '::1', 'localhost', '192.168.', '10.0.', '172.16.');

$is_local = false;
foreach ($whitelist as $ip_part) {
    if (strpos($_SERVER['REMOTE_ADDR'], $ip_part) === 0) {
        $is_local = true;
        break;
    }
}

if ($is_local) {
    $host = '127.0.0.1';
    $user = 'root';
    $pass = ''; 
    $db   = 'ridebuddy_db';
} else {
    // Production / InfinityFree Configuration
    $host = 'sql303.infinityfree.com'; 
    $user = 'if0_40727472';            
    $pass = 'p2aGX4aXQ8';     
    // IMPORTANT: I assumed you named the DB 'ridebuddy' in the panel. 
    // If you named it something else, change 'ridebuddy' below to that name.
    $db   = 'if0_40727472_ridebuddy';  
}

$conn = @new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "Database Server Connection Failed: " . $conn->connect_error . "\n\nIs MySQL started in XAMPP?"]);
    exit;
}

if (!$conn->select_db($db)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "Database '$db' not found.\n\nYou must run http://localhost/Ridebuddy/setup_db.php once to create the database."]);
    exit;
}


