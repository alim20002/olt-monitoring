<?php
include('../header.php');
include('../nav.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .copy-icon {
            cursor: pointer;
            margin-left: 8px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Preview Saved ONU Data</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search ONU data...">
        <div class="mb-3">
            <label for="tableSelect" class="form-label">Select Date Table:</label>
            <select id="tableSelect" class="form-select">
                <option value="" disabled selected>Select a table</option>
                <!-- Table options will be dynamically added here -->
            </select>
        </div>

        <div id="dataContainer" class="table-container mt-4" style="display:none;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>User Name</th>
                        <th>ONU ID</th>
                        <th>MAC Address</th>
                        <th>RX Laser (dBm)</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>

        <div id="loading" class="text-center" style="display:none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="noDataMessage" class="alert alert-warning mt-3" style="display:none;">No data available for the selected table.</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetchTables();

            document.getElementById('tableSelect').addEventListener('change', function () {
                const selectedTable = this.value;
                fetchTableData(selectedTable);
            });
        });

        async function fetchTables() {
            try {
                const response = await fetch('../backend/get_tables.php');
                const result = await response.json();
                const tableSelect = document.getElementById('tableSelect');

                if (result.status === 'success') {
                    result.tables.forEach(tableName => {
                        const option = document.createElement('option');
                        option.value = tableName;
                        option.textContent = tableName;
                        tableSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error fetching tables: ' + error.message);
            }
        }

        async function fetchTableData(tableName) {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('dataContainer').style.display = 'none';
            document.getElementById('noDataMessage').style.display = 'none';

            try {
                const response = await fetch(`../backend/get_table_data.php?table=${tableName}`);
                const result = await response.json();
                const dataTableBody = document.getElementById('dataTableBody');

                if (result.status === 'success' && result.data.length > 0) {
                    dataTableBody.innerHTML = '';
                    let serialCounter = 0;

                    result.data.forEach(onu => {
                        serialCounter++;
                        const row = `
                            <tr>
                                <td>${serialCounter}</td>
                                <td>${onu.username}</td>
                                <td>${onu.onu_id}</td>
                                <td class="mac-address">
                                    ${onu.mac_address}
                                    <i class="fas fa-copy copy-icon" onclick="copyToClipboard('${onu.mac_address}')"></i>
                                </td>
                                <td>${onu.rx_laser}</td>
                            </tr>`;
                        dataTableBody.insertAdjacentHTML('beforeend', row);
                    });

                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('dataContainer').style.display = 'block';
                } else {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('noDataMessage').style.display = 'block';
                }
            } catch (error) {
                console.error('Error fetching table data: ' + error.message);
                document.getElementById('loading').style.display = 'none';
            }
        }

        function copyToClipboard(macAddress) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(macAddress).then(() => {
                    alert(`Copied: ${macAddress}`);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = macAddress;
                document.body.appendChild(tempInput);
                tempInput.select();
                try {
                    document.execCommand('copy');
                    alert(`Copied: ${macAddress}`);
                } catch (err) {
                    console.error('Failed to copy: ', err);
                }
                document.body.removeChild(tempInput);
            }
        }
        document.getElementById('searchBar').addEventListener('input', searchTable);
    </script>
</body>

</html>
