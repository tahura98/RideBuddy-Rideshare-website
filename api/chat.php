<?php
require 'db.php';
session_start();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($action === 'get') {
        $rideId = intval($_GET['rideId']);
        $sql = "SELECT * FROM chat_messages WHERE ride_id=$rideId ORDER BY created_at ASC";
        $result = $conn->query($sql);
        $msgs = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                // formatting for frontend
                $row['time'] = date("h:i A", strtotime($row['created_at']));
                $msgs[] = $row;
            }
        }
        echo json_encode($msgs);
    }
}
elseif ($method === 'POST') {
     $data = json_decode(file_get_contents("php://input"), true);
     
     if ($action === 'send') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        
        if (!$data || !isset($data['rideId']) || !isset($data['text'])) {
             echo json_encode(['status' => 'error', 'message' => 'Missing inputs']);
             exit;
        }
        
        $rideId = intval($data['rideId']);
        $text = $conn->real_escape_string($data['text']);
        $senderId = $_SESSION['user_id'];
        $senderName = $_SESSION['user']['name'] ?? 'User';
        
        if ($rideId <= 0 || empty($text)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid inputs']);
            exit;
        }
        
        $sql = "INSERT INTO chat_messages (ride_id, sender_id, sender_name, message) VALUES ($rideId, $senderId, '$senderName', '$text')";
        if ($conn->query($sql)) {
             // Return updated list
             $all = [];
             $res = $conn->query("SELECT * FROM chat_messages WHERE ride_id=$rideId ORDER BY created_at ASC");
             if ($res) {
                 while($r = $res->fetch_assoc()) {
                     $r['time'] = date("h:i A", strtotime($r['created_at']));
                     $all[] = $r;
                 }
             }
             echo json_encode($all);
        } else {
             echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
     }
}
}
?>
