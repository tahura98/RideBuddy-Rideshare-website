<?php
require_once 'db.php';

header("Content-Type: application/json");

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? '';


function isAdmin($conn, $userId) {
    if (!$userId) return false;
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['role'] === 'admin';
    }
    return false;
}

$adminId = $input['admin_id'] ?? $_GET['admin_id'] ?? 0;

if (!isAdmin($conn, $adminId)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Admin access only']);
    exit;
}

switch ($action) {
    case 'getDashboardStats':
        // Count Users
        $users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
        // Count Rides
        $rides = $conn->query("SELECT COUNT(*) as c FROM rides")->fetch_assoc()['c'];
        // Count Bookings
        $bookings = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
        
        echo json_encode(['status' => 'success', 'stats' => [
            'total_users' => $users,
            'total_rides' => $rides,
            'total_bookings' => $bookings
        ]]);
        break;

    case 'getAllUsers':
        $result = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['status' => 'success', 'users' => $users]);
        break;

    case 'deleteUser':
        $targetId = $input['target_id'] ?? 0;
        if (!$targetId) { echo json_encode(['status' => 'error', 'message' => 'No ID']); exit; }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $targetId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    case 'getAllRides':
        $result = $conn->query("SELECT r.id, r.from_location, r.to_location, r.date, r.status, u.name as driver_name 
                                FROM rides r 
                                JOIN users u ON r.driver_id = u.id 
                                ORDER BY r.date DESC");
        $rides = [];
        while ($row = $result->fetch_assoc()) {
            $rides[] = $row;
        }
        echo json_encode(['status' => 'success', 'rides' => $rides]);
        break;

    case 'deleteRide':
        $targetId = $input['target_id'] ?? 0;
        if (!$targetId) { echo json_encode(['status' => 'error', 'message' => 'No ID']); exit; }

        $stmt = $conn->prepare("DELETE FROM rides WHERE id = ?");
        $stmt->bind_param("i", $targetId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
