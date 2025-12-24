<?php
// Production Database Configuration
// 1. Rename this file to: db.php
// 2. Place it in the 'includes/' folder on your live server
// 3. Fill in your live database credentials below

// Host is usually 'localhost' even on live servers
$host = "localhost";

// YOUR LIVE DATABASE CREDENTIALS
$user = "u12345_username"; // Change this to your cPanel DB Username
$pass = "your_password";   // Change this to your cPanel DB Password
$db   = "u12345_dbname";   // Change this to your cPanel DB Name

try {
    // Determine which credentials to use (Production vs Local fallback)
    // This allows you to accidentally run this locally without breaking everything if you wanted
    if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
        $host = "localhost";
        $user = "root";
        $pass = "";
        $db = "skillgap_db";
    }

    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
