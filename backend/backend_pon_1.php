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
    $olt_ip = '103.134.26.91'; // Your OLT IP
    $port = 8023;
    $username = 'root';
    $password = '123400';

    // Commands to send to the OLT
    $commands = [
        "$username", // Login username
        "$password", // Login password
        "en",        // Enable mode
        "config",    // Enter configuration mode
        "interface epon 0/1", // Specify the interface
        "show ont optical-info 1 all" // Fetch ONU optical info
    ];

    // Begin the Telnet connection in parallel with TXT loading
    $oltResponse = null;
    $usernames = null;

    // Start Telnet connection as a background task
    $telnetTask = function() use ($olt_ip, $port, $commands) {
        return telnet($olt_ip, $port, $commands);
    };

    // Load TXT in parallel
    $txtFile = 'https://raw.githubusercontent.com/alim20002/olt-monitoring/refs/heads/main/user-list.txt'; // Remote URL of the TXT file
    $txtTask = function() use ($txtFile) {
        return loadTXT($txtFile);
    };

    // Execute both tasks in parallel
    $oltResponse = $telnetTask(); // This can be made asynchronous with a better parallel approach
    $usernames = $txtTask(); // This should run simultaneously or before Telnet completes

    // Parse the response from the OLT
    $data = [];
    $lines = explode("\n", $oltResponse);

    // Filter and parse relevant lines based on expected format
    foreach ($lines as $line) {
        if (preg_match('/^  0\/1  1  \d+/', $line)) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 9) { // Adjust this based on actual data format
                $macAddress = strtoupper($parts[3] ?? ''); // Convert MAC address to uppercase
                $data[] = [
                    'pon_port' => $parts[1] ?? '',
                    'onu_id' => $parts[2] ?? '',
                    'mac' => $macAddress,
                    'rx_laser' => $parts[6] ?? '', // Assuming the RX Laser is at index 6
                    'temperature' => $parts[8] ?? '', // Assuming the Temperature is at index 8
                    'username' => 'Unknown' // Default username
                ];
            }
        }
    }

    // Now update usernames in the data array using the loaded TXT data
    foreach ($data as &$onu) {
        if (isset($usernames[$onu['mac']])) {
            $onu['username'] = $usernames[$onu['mac']];
        }
    }

    // Return the parsed data as a JSON response
    echo json_encode(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    // Return error message in case of failure
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
