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
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            text-align: center;
        }
        
        .mac-address {
            position: relative;
            text-align: center;
        }
        
        .copy-icon {
            position: absolute;
            bottom: 5px;
            right: 5px;
            cursor: pointer;
            color: rgba(0, 123, 255, 0.3); /* 30% opacity */
            font-size: 0.6rem; /* Smaller size */
        }

        #loading {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Offline ONU</h2>
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search ONU data...">
        <div id="totalCount" class="mb-3">
            Total ONU Count: <strong id="onuCount">0</strong>
        </div>
        <div id="dataContainer" class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Username</th>
                        <th>ONU ID</th>
                        <th>MAC Address</th>
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
                const response = await fetch('../backend/offline.php');
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
                        </tr>`;
                        dataTableBody.insertAdjacentHTML('beforeend', row);
                    });

                    onuCount.textContent = onuCounter;
                    applyRowColors();
                } else {
                    dataTableBody.innerHTML = '';
                    onuCount.textContent = '0';
                    console.error(result.message);
                }
            } catch (error) {
                console.error('Error fetching data:', error.message);
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
            navigator.clipboard.writeText(macAddress).then(() => {
                alert(`Copied: ${macAddress}`);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
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
