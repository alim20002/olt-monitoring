<?php
// Database connection details
$hostname = 'sql12.freesqldatabase.com'; // Server name
$username = 'sql12737660'; // Username
$password = 'Ize6GMRKKm'; // Password
$dbname = 'sql12737660'; // Database name
$port = 3306; // Port number

// Create connection
$conn = new mysqli($hostname, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}
?>
