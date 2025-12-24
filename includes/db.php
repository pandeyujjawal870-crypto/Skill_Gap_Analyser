<?php
// Database Configuration
// Priority 1: Environment Variables (Railway / Cloud)
if (getenv('MYSQLHOST')) {
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT');
    $host .= ":" . $port; // Append port if using non-standard
} 
// Priority 2: Localhost (XAMPP)
elseif ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "skillgap_db";
} 
// Priority 3: Fallback / Manual
else {
    $host = "localhost";
    $user = "root";
    $pass = "password";
    $db   = "skillgap_db";
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
