<?php
require 'db.php';
session_start();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if ($action === 'submit') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        if (!$data || !isset($data['bookingId']) || !isset($data['stars'])) {
             echo json_encode(['status' => 'error', 'message' => 'Missing inputs']);
             exit;
        }

        $bookingId = intval($data['bookingId']);
        $stars = intval($data['stars']);
        
        // Fetch Booking
        $bReq = $conn->query("SELECT * FROM bookings WHERE id=$bookingId");
        if (!$bReq) {
             echo json_encode(['status' => 'error', 'message' => 'Database error']);
             exit;
        }
        $booking = $bReq->fetch_assoc();
        
        if (!$booking) {
            echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
            exit;
        }
        
        if ($booking['passenger_id'] != $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Not your booking']);
            exit;
        }
        
        // Don't re-rate
        if ($booking['rating_given']) {
             echo json_encode(['status' => 'success', 'message' => 'Already rated']);
             exit;
        }
        
        $rideId = $booking['ride_id'];
        $driverId = $booking['driver_id'];
        $passengerId = $booking['passenger_id'];
        
        $sql = "INSERT INTO ratings (booking_id, ride_id, driver_id, passenger_id, stars) VALUES ($bookingId, $rideId, $driverId, $passengerId, $stars)";
        if ($conn->query($sql)) {
            // Update booking rating_given
            $conn->query("UPDATE bookings SET rating_given=1 WHERE id=$bookingId");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    }
}
elseif ($method === 'GET') {
    if ($action === 'getByBooking') {
        $bookingId = intval($_GET['bookingId']);
        $result = $conn->query("SELECT * FROM ratings WHERE booking_id=$bookingId");
        if ($result && $row = $result->fetch_assoc()) {
             echo json_encode($row);
        } else {
             echo json_encode(null);
        }
    }
}

