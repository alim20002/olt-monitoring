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

// Get all table names starting with 'onu_data_'
$sql = "SHOW TABLES LIKE 'onu_data_%'";
$result = $conn->query($sql);

$tables = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_array()) {
        $tableName = $row[0];
        // Extract the date part from the table name (format: onu_data_DD-MM-YYYY)
        $datePart = str_replace('onu_data_', '', $tableName);
        // Convert the date part to DateTime object for sorting
        $dateObj = DateTime::createFromFormat('d-m-Y', $datePart);
        if ($dateObj) {
            $tables[] = ['name' => $tableName, 'date' => $dateObj];
        }
    }

    // Sort the tables by date (latest first)
    usort($tables, function ($a, $b) {
        return $b['date'] <=> $a['date'];
    });

    // Extract only the table names after sorting
    $sortedTableNames = array_map(function($table) {
        return $table['name'];
    }, $tables);
    
    echo json_encode(['status' => 'success', 'tables' => $sortedTableNames]);
} else {
    echo json_encode(['status' => 'success', 'tables' => []]);
}

$conn->close();
?>
