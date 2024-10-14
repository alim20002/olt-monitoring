<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database credentials
$hostname = 'sql12.freesqldatabase.com';
$username = 'sql12737660';
$password = 'Ize6GMRKKm';
$dbname = 'sql12737660';
$port = 3306;

// Connect to the database
$conn = new mysqli($hostname, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the POST data
$input = json_decode(file_get_contents('php://input'), true);

// Debugging: print the input data
if (!$input) {
    die(json_encode(['status' => 'error', 'message' => 'No input data received.']));
}

// Check if data is properly received
if (!isset($input['data']) || !isset($input['date'])) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid input data.']));
}

$data = $input['data'];
$date = $input['date'];

// Debugging: print received data
error_log(print_r($data, true));
error_log("Received date: $date");

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO onu_data (serial, username, onu_id, mac, rx_laser, temperature, date) VALUES (?, ?, ?, ?, ?, ?, ?)");

// Check if preparation was successful
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]));
}

// Loop through the data and insert it into the database
foreach ($data as $row) {
    // Debugging: print each row before insertion
    error_log(print_r($row, true));

    $stmt->bind_param(
        'sssssss',
        $row['serial'], 
        $row['username'], 
        $row['onu_id'], 
        $row['mac'], 
        $row['rx_laser'], 
        $row['temperature'], 
        $date
    );

    // Execute the query and check if it was successful
    if (!$stmt->execute()) {
        die(json_encode(['status' => 'error', 'message' => 'Failed to insert data: ' . $stmt->error]));
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Send a success response
echo json_encode(['status' => 'success']);
?>
