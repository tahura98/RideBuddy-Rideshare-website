<?php
require 'db.php';
session_start();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($action === 'getAll') {
        $sql = "SELECT * FROM rides WHERE status != 'Closed' ORDER BY date ASC, time ASC";
        $result = $conn->query($sql);
        $rides = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $rides[] = $row;
            }
        }
        echo json_encode($rides);
    }
    elseif ($action === 'getById') {
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM rides WHERE id = $id";
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
    
    if ($action === 'add') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        
        $driver_id = $_SESSION['user_id'];
        $driver_name = $_SESSION['user']['name'];
        
        $from = $conn->real_escape_string($data['from_location']);
        $to = $conn->real_escape_string($data['to_location']);
        $date = $conn->real_escape_string($data['date']);
        $time = $conn->real_escape_string($data['time']);
        $fare = floatval($data['fare_per_seat']);
        $seats = intval($data['available_seats']);
        $pref = $conn->real_escape_string($data['passenger_preference']);
        $note = $conn->real_escape_string($data['note'] ?? '');
        
        $sql = "INSERT INTO rides (driver_id, driver_name, from_location, to_location, date, time, fare_per_seat, available_seats, passenger_preference, note) 
                VALUES ($driver_id, '$driver_name', '$from', '$to', '$date', '$time', $fare, $seats, '$pref', '$note')";
                
        if ($conn->query($sql)) {
            $data['id'] = $conn->insert_id;
            $data['status'] = 'Active';
            echo json_encode(['status' => 'success', 'ride' => $data]);
        } else {
             echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    }
    elseif ($action === 'update') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        $id = intval($_GET['id']);
        $check = $conn->query("SELECT driver_id FROM rides WHERE id=$id");
        $row = $check->fetch_assoc();
        if (!$row || $row['driver_id'] != $_SESSION['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Not owner']);
            exit;
        }
        
        $fields = [];
        if (isset($data['available_seats'])) $fields[] = "available_seats=" . intval($data['available_seats']);
        if (isset($data['status'])) $fields[] = "status='" . $conn->real_escape_string($data['status']) . "'";
        
        if (!empty($fields)) {
            $sql = "UPDATE rides SET " . implode(', ', $fields) . " WHERE id=$id";
            if ($conn->query($sql)) {
                echo json_encode(['status' => 'success']);
            } else {
                 echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
             echo json_encode(['status' => 'success', 'message' => 'Nothing to update']);
        }
    }
}

