<?php
$hostname = 'sql12.freesqldatabase.com';
$username = 'sql12737660';
$password = 'Ize6GMRKKm';
$database = 'sql12737660';
$port = 3306;

// Create connection
$conn = new mysqli($hostname, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tables that match the 'onu_data_' prefix
$sql = "SHOW TABLES LIKE 'onu_data_%'";
$result = $conn->query($sql);

$tables = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
}

echo json_encode(['status' => 'success', 'tables' => $tables]);

$conn->close();
?>
