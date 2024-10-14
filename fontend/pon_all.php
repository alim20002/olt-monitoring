<?php
include('../header.php');
include('../nav.php');
// Add more headers as needed
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
        .dot-button {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #007bff;
            margin-left: 95%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .dot-button:hover {
            background-color: #0056b3;
        }
        .save-button {
            display: none; /* Initially hide the save button */
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        .save-button.visible {
            display: block;
            opacity: 1;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>All Online ONUs</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search ONU data...">
        <div id="totalCount" class="mb-3">
            Total ONU Count: <strong id="onuCount">0</strong>
            <div class="dot-button" id="toggleSaveButton"></div> <!-- Dot button -->
            <button id="saveDataButton" class="btn btn-primary mt-3 save-button">Save Data</button> <!-- Save button -->
            <div id="loading" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        </div>
        <div id="dataContainer" class="table-container">
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
        
        
    </div>

    <script>
        let onuCounter = 0;
        const correctPin = '12300'; // Set the correct PIN

        async function fetchONUData() {
            document.getElementById('loading').style.display = 'block';
            try {
                const response = await fetch('../backend/backend_pon_all.php');
                const result = await response.json();
                document.getElementById('loading').style.display = 'none';

                const dataTableBody = document.getElementById('dataTableBody');
                const onuCount = document.getElementById('onuCount');

                if (result.status === 'success') {
                    dataTableBody.innerHTML = '';
                    onuCounter = 0;

                    result.data.forEach((onu) => {
                        onuCounter++;
                        const row = `<tr>
                            <td>${onuCounter}</td>
                            <td>${onu.username}</td>
                            <td>${onu.pon_port}:${onu.onu_id}</td>
                            <td class="mac-address">
                                ${onu.mac}
                                <i class="fas fa-copy copy-icon" onclick="copyToClipboard('${onu.mac}')"></i>
                            </td>
                            <td>${onu.rx_laser}</td>
                        </tr>`;
                        dataTableBody.insertAdjacentHTML('beforeend', row);
                    });

                    onuCount.textContent = onuCounter;
                    applyRowColors();
                } else {
                    dataTableBody.innerHTML = '';
                    onuCount.textContent = '0';
                }
            } catch (error) {
                console.error('Error fetching data: ' + error.message);
                document.getElementById('loading').style.display = 'none';
            }
        }

        function applyRowColors() {
            const rows = document.querySelectorAll('#dataTableBody tr');
            rows.forEach((row, index) => {
                row.className = index % 2 === 0 ? 'table-light' : 'table-secondary';
            });
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

        function searchTable() {
            const filter = document.getElementById('searchBar').value.toLowerCase();
            const rows = document.querySelectorAll('#dataTableBody tr');

            rows.forEach((row) => {
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let i = 0; i < cells.length; i++) {
                    if (cells[i].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                row.style.display = found ? '' : 'none';
            });

            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
            visibleRows.forEach((row, index) => {
                row.className = index % 2 === 0 ? 'table-light' : 'table-secondary';
            });
        }

        document.getElementById('searchBar').addEventListener('input', searchTable);

        document.getElementById('saveDataButton').addEventListener('click', async () => {
            // Show the loading spinner when saving
            document.getElementById('loading').style.display = 'block';

            const rows = document.querySelectorAll('#dataTableBody tr');
            const tableData = [];

            rows.forEach(row => {
                const columns = row.querySelectorAll('td');
                const rowData = {
                    serial: columns[0].textContent,
                    username: columns[1].textContent,
                    onuId: columns[2].textContent,
                    macAddress: columns[3].textContent,
                    rxLaser: columns[4].textContent
                };
                tableData.push(rowData);
            });

            try {
                const response = await fetch('../backend/save_data.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(tableData)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert('Data saved successfully!');
                } else {
                    alert('Error saving data.');
                }
            } catch (error) {
                console.error('Error saving data: ' + error.message);
            } finally {
                // Hide the loading spinner after saving
                document.getElementById('loading').style.display = 'none';
            }
        });

        // Handle dot button click for showing/hiding save button with PIN code and auto-hide after 10 seconds
        const dotButton = document.getElementById('toggleSaveButton');
        const saveButton = document.getElementById('saveDataButton');

        dotButton.addEventListener('click', () => {
            const userPin = prompt('Enter PIN to show Save Button:');
            if (userPin === correctPin) {
                saveButton.classList.add('visible');

                // Automatically hide the save button after 10 seconds
                setTimeout(() => {
                    saveButton.classList.remove('visible');
                }, 5000); // 5 seconds in milliseconds
            } else {
                alert('Incorrect PIN');
            }
        });

        fetchONUData();
    </script>
</body>

</html>
