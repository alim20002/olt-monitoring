<?php
function telnet($host, $port, $commands, $timeout = 30) {
    $connection = fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$connection) {
        die("Connection failed: $errstr ($errno)");
    }

    stream_set_blocking($connection, false);
    $response = '';

    // Send commands and collect responses
    foreach ($commands as $command) {
        fwrite($connection, $command . "\n");
        // Give some time for the command to execute
        usleep(500000); // 0.5 seconds
        while ($line = fgets($connection)) {
            $response .= $line;
        }
    }

    fclose($connection);
    return $response;
}

// OLT connection details
$olt_ip = '103.134.26.91';
$port = 8023;
$username = 'root';
$password = '123400';

// Commands to send (adjust these commands based on your OLT's requirements)
$commands = [
    "$username", // Adjust based on OLT login procedure
    "$password", // Adjust based on OLT login procedure
    "en"
    
];

// Execute commands
$response = telnet($olt_ip, $port, $commands);

// Display the output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLT Monitor Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>OLT Monitor Panel</h1>
        <pre><?php echo htmlspecialchars($response); ?></pre>
    </div>
</body>
</html>
