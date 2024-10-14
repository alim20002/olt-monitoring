<?php
$hostname = 'sql12.freesqldatabase.com';
$username = 'sql12737660';
$password = 'Ize6GMRKKm';
$database = 'sql12737660';
$port = 3306;

$conn = new mysqli($hostname, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table_name = $_GET['table'];

$sql = "SELECT * FROM `$table_name`";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'username' => $row['username'],
            'onu_id' => $row['onu_id'],
            'mac_address' => $row['mac_address'],
            'rx_laser' => $row['rx_laser'],
            'save_date' => $row['save_date'],
            'save_time' => $row['save_time']
        ];
    }
}

echo json_encode(['status' => 'success', 'data' => $data]);

$conn->close();
?>
