<?php
require 'db.php';
session_start();

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($action === 'register') {
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);
        $role = $conn->real_escape_string($data['role']);
        $password = $data['password']; 
        // Basic Validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@iub.edu.bd')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email domain']);
            exit;
        }

        $check = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            exit;
        }

        $sql = "INSERT INTO users (name, email, role, password) VALUES ('$name', '$email', '$role', '$password')";
        if ($conn->query($sql)) {
            $last_id = $conn->insert_id;
            $user = [
                'id' => $last_id,
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            $_SESSION['user_id'] = $last_id;
            $_SESSION['user'] = $user;
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    } 
    elseif ($action === 'login') {
        $email = $conn->real_escape_string($data['email']);
        $password = $data['password'];

        $result = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$password'");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            unset($user['password']); 
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            echo json_encode(['status' => 'success', 'user' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    }
    elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['status' => 'success']);
    }
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'session') {
        if (isset($_SESSION['user'])) {
            echo json_encode(['status' => 'success', 'user' => $_SESSION['user']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        }
    }
}

