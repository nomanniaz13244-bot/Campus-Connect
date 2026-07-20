<?php
// Database Configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'campus_connect';

// Connection
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', 'http://localhost/Campus-Connect/');
?>