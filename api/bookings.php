<?php
require 'db.php';
session_start();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($action === 'getByUser') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([]);
            exit;
        }
        $userId = $_SESSION['user_id'];
        $sql = "SELECT * FROM bookings WHERE passenger_id = $userId ORDER BY created_at DESC";
        $result = $conn->query($sql);
        $bookings = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
        }
        echo json_encode($bookings);
    }
    elseif ($action === 'getById') {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM bookings WHERE id = $id";
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            echo json_encode($row);
        } else {
            echo json_encode(null);
        }
    }
}
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($action === 'create') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $rideId = intval($data['rideId']);
        $seats = intval($data['seats']);
        $passengerId = $_SESSION['user_id'];
        $passengerName = $_SESSION['user']['name'];

        // Get Ride
        $ride = $conn->query("SELECT * FROM rides WHERE id=$rideId")->fetch_assoc();
        if (!$ride) {
            echo json_encode(['status' => 'error', 'message' => 'Ride not found']);
            exit;
        }

        if ($ride['available_seats'] < $seats) {
            echo json_encode(['status' => 'error', 'message' => 'Not enough seats']);
            exit;
        }
        
        $driverId = $ride['driver_id'];
        $driverName = $ride['driver_name'];
        $fare = $ride['fare_per_seat'];

        // Start Transaction
        $conn->begin_transaction();
        try {
            // Insert Booking
            $sql = "INSERT INTO bookings (ride_id, passenger_id, passenger_name, driver_id, driver_name, seats, fare_per_seat, status) VALUES ($rideId, $passengerId, '$passengerName', $driverId, '$driverName', $seats, $fare, 'PENDING')";
            $conn->query($sql);
            $bookingId = $conn->insert_id;

            // Update Ride Seats
            $conn->query("UPDATE rides SET available_seats = available_seats - $seats WHERE id=$rideId");

            $conn->commit();
            
            // Return full booking object
            $bReq = $conn->query("SELECT * FROM bookings WHERE id=$bookingId");
            echo json_encode(['status' => 'success', 'booking' => $bReq->fetch_assoc()]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    elseif ($action === 'pay') {
        $bookingId = intval($data['bookingId']);
        $method = $conn->real_escape_string($data['method']);

        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO payments (booking_id, method) VALUES ($bookingId, '$method')");
            $conn->query("UPDATE bookings SET status='PAID', paid_at=NOW() WHERE id=$bookingId");
            
          
            $conn->commit();
            
            // Return updated booking
             $bReq = $conn->query("SELECT * FROM bookings WHERE id=$bookingId");
             echo json_encode(['status' => 'success', 'booking' => $bReq->fetch_assoc()]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    elseif ($action === 'complete') {
        $bookingId = intval($data['bookingId']);
        $conn->query("UPDATE bookings SET status='COMPLETED', completed_at=NOW() WHERE id=$bookingId");
        
        $bReq = $conn->query("SELECT * FROM bookings WHERE id=$bookingId");
        echo json_encode(['status' => 'success', 'booking' => $bReq->fetch_assoc()]);
    }
}

