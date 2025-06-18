<?php
header('Content-Type: application/json');

// Function to perform Telnet connection and execute commands
function telnet($host, $port, $commands, $timeout = 30) {
    $connection = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$connection) {
        return "Connection failed: $errstr ($errno)";
    }

    stream_set_blocking($connection, false);
    $response = '';

    // Send commands and collect responses
    foreach ($commands as $command) {
        fwrite($connection, $command . "\n");
        usleep(300000); // Reduced delay to 0.3 seconds between commands
        while ($line = fgets($connection)) {
            $response .= $line;
        }
    }

    fclose($connection);
    return $response;
}

// Function to load TXT and map MAC addresses to usernames from a remote URL or local file
function loadTXT($txtFile) {
    $usernames = [];
    
    // Check if it's a URL or a local file
    if (filter_var($txtFile, FILTER_VALIDATE_URL)) {
        $fileContent = file_get_contents($txtFile); // Fetch the file content from URL
        if ($fileContent !== false) {
            $lines = explode("\n", $fileContent);
        } else {
            return []; // Return an empty array if fetching failed
        }
    } else if (file_exists($txtFile)) {
        $lines = file($txtFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); // Local file loading
    } else {
        return [];
    }

    // Process each line to map usernames to MAC addresses
    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', trim($line), 2); // Split by whitespace into 2 parts
        $username = trim($parts[0] ?? ''); // Username
        $mac = strtoupper(trim($parts[1] ?? '')); // MAC address (uppercase)
        if (!empty($mac)) {
            $usernames[$mac] = $username;
        }
    }

    return $usernames;
}

try {
    // OLT connection details
    $olt_ip = '103.113.148.176'; // Your OLT IP
    $port = 8023;
    $username = 'root';
    $password = '123400';

    // Commands to send to the OLT
    $commands = [
        "$username", // Login username
        "$password", // Login password
        "en",        // Enable mode
        "config",    // Enter configuration mode
        "interface epon 0/1" // Specify the interface
    ];

    // Begin the Telnet connection in parallel with TXT loading
    $oltResponse = null;
    $usernames = null;

    // Load usernames from TXT
    $txtFile = 'https://raw.githubusercontent.com/alim20002/olt-monitoring/refs/heads/main/user-list.txt'; // Remote URL of the TXT file
    $usernames = loadTXT($txtFile);

    // Collect responses for all ports
    $data = [];
    for ($i = 1; $i <= 4; $i++) {
        // Prepare commands for the current port
        $currentCommands = array_merge($commands, ["show ont optical-info $i all"]);
        
        // Start Telnet connection and get response for the current port
        $oltResponse = telnet($olt_ip, $port, $currentCommands);

        // Parse the response from the OLT for the current port
        $lines = explode("\n", $oltResponse);

        // Filter and parse relevant lines based on expected format
        foreach ($lines as $line) {
            if (preg_match('/^  0\/1  \d+/', $line)) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 8) { // Adjust this based on actual data format
                    $macAddress = strtoupper($parts[3] ?? ''); // Convert MAC address to uppercase
                    $data[] = [
                        'pon_port' => $parts[1] ?? '',
                        'onu_id' => $parts[2] ?? '',
                        'mac' => $macAddress,
                        'rx_laser' => $parts[6] ?? '', // Assuming the RX Laser is at index 6
                        'username' => isset($usernames[$macAddress]) ? $usernames[$macAddress] : 'Unknown' // Default username
                    ];
                }
            }
        }
    }

    // Return the parsed data as a JSON response
    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    // Return error message in case of failure
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
