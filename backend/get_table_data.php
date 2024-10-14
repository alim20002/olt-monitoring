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

// Get the table name from the query parameter
$tableName = $_GET['table'];

// Fetch the data from the selected table
$sql = "SELECT * FROM `$tableName`";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'serial' => $row['serial'],
            'username' => $row['username'],
            'onu_id' => $row['onu_id'],
            'mac_address' => $row['mac_address'],
            'rx_laser' => $row['rx_laser']
        ];
    }
}

if (!empty($data)) {
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'data' => []]);
}

$conn->close();
?>
