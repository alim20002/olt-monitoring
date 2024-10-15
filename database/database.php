<?php
// Database connection
$hostname = 'sql12.freesqldatabase.com';
$username = 'sql12737660';
$password = 'Ize6GMRKKm';
$database = 'sql12737660';
$port = 3306;

$conn = new mysqli($hostname, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the operations
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'fetchTables') {
    // Fetch all table names starting with 'onu_data_'
    $sql = "SHOW TABLES LIKE 'onu_data_%'";
    $result = $conn->query($sql);

    $tables = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
    }
    echo json_encode(['status' => 'success', 'tables' => $tables]);

} elseif ($action == 'fetchData') {
    // Fetch data from selected table
    $table = $_POST['tableName'];
    $sql = "SELECT * FROM `$table`";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'data' => $data]);

} elseif ($action == 'updateRow') {
    // Update a row in the selected table
    $table = $_POST['tableName'];
    $id = $_POST['id'];
    $column = $_POST['column'];
    $value = $_POST['value'];

    $sql = "UPDATE `$table` SET `$column` = '$value' WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }

} elseif ($action == 'deleteRow') {
    // Delete a row in the selected table
    $table = $_POST['tableName'];
    $id = $_POST['id'];

    $sql = "DELETE FROM `$table` WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }

} elseif ($action == 'deleteTable') {
    // Delete the selected table
    $table = $_POST['tableName'];

    $sql = "DROP TABLE `$table`";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}

$conn->close();
?>
