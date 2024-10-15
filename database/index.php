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
    echo json_encode(['tables' => $tables]);
    exit;
}

if ($action == 'fetchData') {
    $tableName = $_POST['tableName'];
    $sql = "SELECT * FROM `$tableName`";
    $result = $conn->query($sql);

    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(['data' => $data]);
    exit;
}

if ($action == 'updateRow') {
    $tableName = $_POST['tableName'];
    $id = $_POST['id'];
    $column = $_POST['column'];
    $value = $_POST['value'];

    $sql = "UPDATE `$tableName` SET `$column` = '$value' WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

if ($action == 'deleteRow') {
    $tableName = $_POST['tableName'];
    $id = $_POST['id'];

    $sql = "DELETE FROM `$tableName` WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

if ($action == 'deleteTable') {
    $tableName = $_POST['tableName'];

    $sql = "DROP TABLE `$tableName`";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h2, h3 {
            color: #333;
        }
        .select-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        #tableSelect {
            padding: 10px;
            font-size: 16px;
            margin-right: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
        }
        .btn {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 10px;
        }
        .btn-danger {
            background-color: red;
        }
        .btn-save {
            background-color: #28a745;
            margin-left: 10px;
            display: none; /* Initially hidden */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .delete-mark {
            cursor: pointer;
        }
        .delete-mark:checked + td {
            background-color: #f8d7da;
        }
        .table-actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .select-all {
            cursor: pointer;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<h2>Manage Tables</h2>
<div class="select-container">
    <select id="tableSelect" class="form-control" onchange="fetchTableData()">
        <option value="">Select Table</option>
    </select>
    <button class="btn btn-danger" onclick="deleteTable()">Delete Table</button>
</div>

<div class="table-actions">
    <h3>Table Data</h3>
    <button class="btn btn-danger" id="deleteSelectedButton" style="display: none;" onclick="deleteMarkedRows()">Delete Selected Rows</button>
</div>

<div id="notificationContainer"></div>
<div id="tableContainer"></div>

<script>
    let saveButtons = {};
    let lastChecked = null;

    window.onload = function() {
        const password = prompt("Enter the password to access this page:");
        if (password !== "12300") {
            alert("Incorrect password. Access denied.");
            window.location.href = "about:blank"; // Redirect to a blank page
        } else {
            fetchTables();
        }
    }

    function showNotification(message, type = 'success') {
        const notificationContainer = document.getElementById('notificationContainer');
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `<div class="alert ${alertClass}" role="alert">${message}</div>`;
        notificationContainer.innerHTML = alertHtml;
        setTimeout(() => {
            notificationContainer.innerHTML = '';
        }, 3000); // Hide after 3 seconds
    }

    function fetchTables() {
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=fetchTables'
        })
        .then(response => response.json())
        .then(data => {
            const tableSelect = document.getElementById('tableSelect');
            tableSelect.innerHTML = '<option value="">Select Table</option>';
            data.tables.forEach(table => {
                const option = document.createElement('option');
                option.value = table;
                option.textContent = table;
                tableSelect.appendChild(option);
            });
        });
    }

    function fetchTableData() {
        const tableName = document.getElementById('tableSelect').value;
        if (!tableName) return;

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=fetchData&tableName=${tableName}`
        })
        .then(response => response.json())
        .then(data => {
            const tableContainer = document.getElementById('tableContainer');
            if (data.data.length > 0) {
                let tableHtml = '<table class="table table-bordered"><thead><tr>';
                tableHtml += '<th><input type="checkbox" class="select-all" onclick="toggleSelectAll(this)"> Select All</th>';
                Object.keys(data.data[0]).forEach(key => {
                    tableHtml += `<th>${key}</th>`;
                });
                tableHtml += '<th>Actions</th></tr></thead><tbody>';

                data.data.forEach(row => {
                    tableHtml += '<tr>';
                    tableHtml += `<td><input type="checkbox" class="delete-mark" onclick="toggleDeleteButton()"></td>`;
                    Object.keys(row).forEach(key => {
                        tableHtml += `<td contenteditable="true" data-original="${row[key]}" onblur="editCell(this, '${key}', '${row.id}')">${row[key]}</td>`;
                    });
                    tableHtml += `<td><button class="btn btn-danger" onclick="deleteRow('${row.id}')">Delete</button> 
                                  <button class="btn btn-save" id="save-${row.id}" onclick="saveRow('${row.id}')">Save</button></td>`;
                    tableHtml += '</tr>';
                });

                tableHtml += '</tbody></table>';
                tableContainer.innerHTML = tableHtml;
            } else {
                tableContainer.innerHTML = '<p>No data found.</p>';
            }
        });
    }

    function editCell(cell, column, id) {
        const originalValue = cell.getAttribute('data-original');
        const newValue = cell.textContent.trim();

        if (originalValue !== newValue) {
            document.getElementById(`save-${id}`).style.display = 'inline-block';
            saveButtons[id] = { column, value: newValue };
        } else {
            document.getElementById(`save-${id}`).style.display = 'none';
            delete saveButtons[id];
        }
    }

    function saveRow(id) {
        const { column, value } = saveButtons[id];
        const tableName = document.getElementById('tableSelect').value;

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=updateRow&tableName=${tableName}&id=${id}&column=${column}&value=${value}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Row updated successfully.');
                document.getElementById(`save-${id}`).style.display = 'none';
                delete saveButtons[id];
            } else {
                showNotification('Failed to update row.', 'error');
            }
        });
    }

    function deleteRow(id) {
        const tableName = document.getElementById('tableSelect').value;

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=deleteRow&tableName=${tableName}&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Row deleted successfully.');
                fetchTableData();
            } else {
                showNotification('Failed to delete row.', 'error');
            }
        });
    }

    function deleteTable() {
        const tableName = document.getElementById('tableSelect').value;
        if (!tableName) return;

        if (confirm('Are you sure you want to delete the table?')) {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=deleteTable&tableName=${tableName}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('Table deleted successfully.');
                    fetchTables();
                    document.getElementById('tableContainer').innerHTML = '';
                } else {
                    showNotification('Failed to delete table.', 'error');
                }
            });
        }
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.delete-mark');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        toggleDeleteButton();
    }

    function toggleDeleteButton() {
        const deleteSelectedButton = document.getElementById('deleteSelectedButton');
        const checkboxes = document.querySelectorAll('.delete-mark:checked');
        if (checkboxes.length > 0) {
            deleteSelectedButton.style.display = 'inline-block';
        } else {
            deleteSelectedButton.style.display = 'none';
        }
    }

    function deleteMarkedRows() {
        const markedRows = document.querySelectorAll('.delete-mark:checked');
        if (markedRows.length === 0) return;

        const tableName = document.getElementById('tableSelect').value;
        const rowIds = Array.from(markedRows).map(row => row.closest('tr').querySelector('td:nth-child(2)').textContent);

        if (confirm(`Are you sure you want to delete ${rowIds.length} rows?`)) {
            rowIds.forEach(id => deleteRow(id));
        }
    }
</script>
</body>
</html>
