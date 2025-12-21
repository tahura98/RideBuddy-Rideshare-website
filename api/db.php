<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");


$whitelist = array('127.0.0.1', '::1', 'localhost');

if(in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
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

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    // Return the specific error so the frontend can show "Access Denied" or "Unknown Database"
    echo json_encode(['status' => 'error', 'message' => "Database Connection Failed: " . $conn->connect_error]);
    exit;
}


