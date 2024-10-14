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
            cursor: pointer; /* Add pointer cursor for the copy icon */
            margin-left: 8px; /* Add space between MAC address and icon */
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>All Online ONUs</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search ONU data...">
        <div id="totalCount" class="mb-3">
            Total ONU Count: <strong id="onuCount">0</strong>
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
                        <th>Temperature (°C)</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
                    <!-- Data will be injected here -->
                </tbody>
            </table>
        </div>
        <div id="loading" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <script>
        let onuCounter = 0;

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
                            <td>${onu.temperature}</td>
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
                // Use the Clipboard API
                navigator.clipboard.writeText(macAddress).then(() => {
                    alert(`Copied: ${macAddress}`);
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            } else {
                // Fallback for older browsers
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

            // Reset row colors after filtering
            const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
            visibleRows.forEach((row, index) => {
                row.className = index % 2 === 0 ? 'table-light' : 'table-secondary';
            });
        }

        document.getElementById('searchBar').addEventListener('input', searchTable);

        // Fetch data when the page loads
        fetchONUData();
    </script>
</body>

</html>
