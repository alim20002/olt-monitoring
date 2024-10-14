<?php
// Database connection details
$hostname = 'sql12.freesqldatabase.com'; // Updated server name
$username = 'sql12737660'; // Updated username
$password = 'Ize6GMRKKm'; // Updated password
$dbname = 'sql12737660'; // Updated database name
$port = 3306; // Port number

// Create connection
$conn = new mysqli($hostname, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Query to fetch data
$sql = "SELECT * FROM onu_data ORDER BY date DESC"; // Ensure the table name is correct
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    // Fetch all rows
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

// Return data as JSON
echo json_encode(['status' => 'success', 'data' => $data]);
?>
