<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'rsoa_rsoa278_17');
define('DB_PASS', '123456');
define('DB_NAME', 'rsoa_rsoa278_17');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Define base URL
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));
?>
