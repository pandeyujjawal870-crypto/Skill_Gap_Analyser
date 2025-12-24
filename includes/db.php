<?php
// Database Configuration (Auto-Detects Environment)
if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // Localhost Credentials (XAMPP)
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "skillgap_db";
} else {
    // Live Server Credentials (EDIT THESE for your Host)
    $host = "localhost";
    $user = "root"; // Change to your Live Username
    $pass = "password";   // Change to your Live Password
    $db   = "skillgap_db";   // Change to your Live DB Name
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
