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
        fetch('database.php', {
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

        fetch('database.php', {
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
        const originalValue = cell.getAttribute('data-original') || cell.textContent;
        const currentValue = cell.textContent;

        if (currentValue !== originalValue) {
            const saveButton = document.getElementById(`save-${id}`);
            saveButton.style.display = 'inline-block'; // Show the save button next to the row
            if (!saveButtons[id]) saveButtons[id] = {};
            saveButtons[id][column] = currentValue;
        } else {
            const saveButton = document.getElementById(`save-${id}`);
            saveButton.style.display = 'none'; // Hide the save button if no changes
        }
    }

    function saveRow(id) {
        const tableName = document.getElementById('tableSelect').value;
        const changes = saveButtons[id] || {};

        Object.keys(changes).forEach(column => {
            const newValue = changes[column];

            fetch('database.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=updateRow&tableName=${tableName}&id=${id}&column=${column}&value=${newValue}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById(`save-${id}`).style.display = 'none'; // Hide the save button after saving
                } else {
                    alert('Failed to update row');
                }
            });
        });
    }

    function deleteRow(id) {
        const tableName = document.getElementById('tableSelect').value;

        fetch('database.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=deleteRow&tableName=${tableName}&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Row deleted successfully.');
                fetchTableData(); // Refresh table data
            } else {
                alert('Failed to delete row');
            }
        });
    }

    function deleteMarkedRows() {
        const tableName = document.getElementById('tableSelect').value;
        const markedRows = Array.from(document.querySelectorAll('.delete-mark:checked')).map(mark => {
            return mark.closest('tr').querySelector('td:nth-child(2)').textContent; // Assuming the first data cell is the ID
        });

        const deletePromises = markedRows.map(id => {
            return fetch('database.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=deleteRow&tableName=${tableName}&id=${id}`
            }).then(response => response.json());
        });

        Promise.all(deletePromises)
        .then(results => {
            const successfulDeletes = results.filter(result => result.status === 'success').length;
            if (successfulDeletes > 0) {
                showNotification(`${successfulDeletes} rows deleted successfully.`);
            }
            fetchTableData(); // Refresh table data
        });
    }

    function deleteTable() {
        const tableName = document.getElementById('tableSelect').value;

        fetch('database.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=deleteTable&tableName=${tableName}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showNotification('Table deleted successfully.');
                fetchTables(); // Refresh table list
                document.getElementById('tableContainer').innerHTML = '';
            } else {
                alert('Failed to delete table');
            }
        });
    }

    function toggleSelectAll(selectAll) {
        const checkboxes = document.querySelectorAll('.delete-mark');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        toggleDeleteButton(); // Check if any checkbox is selected
    }

    function toggleDeleteButton() {
        const checkboxes = document.querySelectorAll('.delete-mark');
        const deleteButton = document.getElementById('deleteSelectedButton');
        const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        deleteButton.style.display = anyChecked ? 'block' : 'none'; // Show or hide the delete button based on checked checkboxes
    }

    function shiftClick(checkbox) {
        if (!lastChecked) {
            lastChecked = checkbox;
            return;
        }
        if (event.shiftKey) {
            const checkboxes = Array.from(document.querySelectorAll('.delete-mark'));
            const start = checkboxes.indexOf(lastChecked);
            const end = checkboxes.indexOf(checkbox);
            const range = start < end ? checkboxes.slice(start, end + 1) : checkboxes.slice(end, start + 1);
            range.forEach(checkbox => checkbox.checked = lastChecked.checked);
            toggleDeleteButton(); // Update the delete button visibility
        }
        lastChecked = checkbox;
    }
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
