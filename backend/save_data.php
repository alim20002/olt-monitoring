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

// Create table if it doesn't exist
$createTableSQL = "
    CREATE TABLE IF NOT EXISTS `$tableName` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        serial INT,
        username VARCHAR(255),
        onu_id VARCHAR(255),
        mac_address VARCHAR(255),
        rx_laser VARCHAR(255),
        save_date DATE,
        save_time TIME
    )";

if (!$conn->query($createTableSQL)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create table: ' . $conn->error]);
    $conn->close();
    exit();
}

// Get posted data
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    // Prepare the insert statement
    $insertSQL = "INSERT INTO `$tableName` (serial, username, onu_id, mac_address, rx_laser, save_date, save_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSQL);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
        $conn->close();
        exit();
    }

    // Get current date and time
    $currentDate = date('Y-m-d'); // Format for the database
    $currentTime = date('H:i:s');

    foreach ($data as $row) {
        if (isset($row['serial'], $row['username'], $row['onuId'], $row['macAddress'], $row['rxLaser'])) {
            // Bind parameters and execute
            $stmt->bind_param("issssss", 
                $row['serial'], 
                $row['username'], 
                $row['onuId'], 
                $row['macAddress'], 
                $row['rxLaser'], 
                $currentDate, 
                $currentTime
            );

            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
                $stmt->close();
                $conn->close();
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing fields in data row']);
            $stmt->close();
            $conn->close();
            exit();
        }
    }

    echo json_encode(['status' => 'success']);
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}

$conn->close();
?>
