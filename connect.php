<?php
// Database connection parameters
$servername = "localhost"; // Usually 'localhost' for local development
$username = "root";        // Your MySQL username
$password = "";            // Your MySQL password (often empty for 'root' on XAMPP/WAMP)
$dbname = "as"; // The database name we created

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set character set to UTF-8
$conn->set_charset("utf8");
?>