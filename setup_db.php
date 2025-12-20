<?php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; // Default XAMPP password

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS ridebuddy_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    die("Error creating database: " . $conn->error);
}

$conn->select_db('ridebuddy_db');

// Users Table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('driver', 'rider', 'both') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) { echo "Table users created successfully<br>"; } else { echo "Error creating table users: " . $conn->error . "<br>"; }

// Rides Table
$sql = "CREATE TABLE IF NOT EXISTS rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    driver_name VARCHAR(100),
    from_location VARCHAR(255) NOT NULL,
    to_location VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    time VARCHAR(20) NOT NULL,
    fare_per_seat DECIMAL(10,2) NOT NULL,
    available_seats INT NOT NULL,
    passenger_preference VARCHAR(50),
    status VARCHAR(20) DEFAULT 'Active',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) { echo "Table rides created successfully<br>"; } else { echo "Error creating table rides: " . $conn->error . "<br>"; }

// Bookings Table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    passenger_id INT NOT NULL,
    passenger_name VARCHAR(100),
    driver_id INT NOT NULL,
    driver_name VARCHAR(100),
    seats INT NOT NULL,
    fare_per_seat DECIMAL(10,2) NOT NULL,
    status ENUM('PENDING', 'PAID', 'COMPLETED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    rating_given TINYINT(1) DEFAULT 0,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) { echo "Table bookings created successfully<br>"; } else { echo "Error creating table bookings: " . $conn->error . "<br>"; }

// Payments Table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    method VARCHAR(50),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) { echo "Table payments created successfully<br>"; } else { echo "Error creating table payments: " . $conn->error . "<br>"; }

// Ratings Table
$sql = "CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    ride_id INT NOT NULL,
    driver_id INT NOT NULL,
    passenger_id INT NOT NULL,
    stars INT NOT NULL CHECK (stars BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) { echo "Table ratings created successfully<br>"; } else { echo "Error creating table ratings: " . $conn->error . "<br>"; }

// Chat Messages Table
$sql = "CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_name VARCHAR(100),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) { echo "Table chat_messages created successfully<br>"; } else { echo "Error creating table chat_messages: " . $conn->error . "<br>"; }

$conn->close();
?>
