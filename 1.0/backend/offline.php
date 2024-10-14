<?php
// Function to handle Telnet connection and command execution
function telnet($host, $port, $commands, $timeout = 30) {
    // Open connection to OLT
    $connection = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$connection) {
        return "Connection failed: $errstr ($errno)";
    }

    stream_set_blocking($connection, false);
    $response = '';

    // Send commands to the OLT
    foreach ($commands as $command) {
        fwrite($connection, $command . "\n");
        usleep(300000); // Delay between commands
        while ($line = fgets($connection)) {
            $response .= $line;
        }
    }

    fclose($connection);
    return $response; // Return the raw response from the OLT
}

// Function to load usernames from a remote TXT file
function loadUsernames($txtFile) {
    $usernames = [];
    
    // Load usernames from the specified URL
    if (filter_var($txtFile, FILTER_VALIDATE_URL)) {
        $fileContent = file_get_contents($txtFile);
        if ($fileContent !== false) {
            $lines = explode("\n", $fileContent);
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line), 2); // Split by whitespace
                $username = trim($parts[0] ?? '');
                $mac = strtoupper(trim($parts[1] ?? ''));
                if (!empty($mac)) {
                    $usernames[$mac] = $username; // Map MAC to username
                }
            }
        }
    }
    
    return $usernames; // Return the array of usernames
}

// OLT details
$olt_ip = '103.134.26.91'; // Replace with your OLT IP
$port = 8023;
$username = 'root';
$password = '123400';

// Load usernames from the TXT file
$txtFile = 'https://raw.githubusercontent.com/alim20002/olt-monitoring/refs/heads/main/user-list.txt'; // URL of the TXT file
$usernames = loadUsernames($txtFile);

// Commands to fetch ONU info
$commands_info = [
    $username,
    $password,
    "en",
    "config",
    "show ont info all" // Fetch all ONU info
];

// Fetch ONU info response via Telnet
$response_info = telnet($olt_ip, $port, $commands_info);

// Initialize an array to hold the extracted ONU data
$onu_data = [];

// Split the response into lines and look for the relevant ONU info
$lines_info = explode("\n", $response_info);
foreach ($lines_info as $line) {
    // Use regex to find lines that match the expected ONU format
    if (preg_match('/(\d+)\s+(\d+)\s+([0-9A-F:]{17})\s+Active\s+Offline/i', $line, $matches)) {
        // Only extract and format the needed parts
        $formatted_onu = [
            'pon_port' => $matches[1],
            'onu_id' => $matches[2],
            'mac' => strtoupper(substr($matches[3], 0, 17)), // Format MAC address
            'username' => $usernames[strtoupper(substr($matches[3], 0, 17))] ?? 'Unknown' // Get username or default to 'Unknown'
        ];
        $onu_data[] = $formatted_onu; // Add the formatted ONU data to the array
    }
}

// Output the extracted ONU data in JSON format
header('Content-Type: application/json');
echo json_encode(['status' => !empty($onu_data) ? 'success' : 'error', 'data' => $onu_data]);
?>
