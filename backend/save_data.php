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

// Get current date for the table name
$date = date('d-m-Y');
$tableName = 'onu_data_' . $date;

// Create table if not exists
$createTableSQL = "
    CREATE TABLE IF NOT EXISTS `$tableName` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial INT,
        username VARCHAR(255),
        onu_id VARCHAR(255),
        mac_address VARCHAR(255),
        rx_laser VARCHAR(255)
    )";

if (!$conn->query($createTableSQL)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create table: ' . $conn->error]);
    $conn->close();
    exit();
}

// Get posted data
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    // Insert data into the newly created table
    $insertSQL = "INSERT INTO `$tableName` (serial, username, onu_id, mac_address, rx_laser) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSQL);

    foreach ($data as $row) {
        $stmt->bind_param("issss", $row['serial'], $row['username'], $row['onuId'], $row['macAddress'], $row['rxLaser']);
        $stmt->execute();
    }

    echo json_encode(['status' => 'success']);
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}

$conn->close();
?>
