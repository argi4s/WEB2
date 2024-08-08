<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Query to fetch the base coordinates
$sql = "SELECT latitude, longitude FROM base LIMIT 1";
$result = $conn->query($sql);

$response = array();

if ($result === FALSE) {
    http_response_code(500);
    echo json_encode(['error' => 'Error executing query: ' . $conn->error]);
    exit();
}

if ($result->num_rows > 0) {
    // Fetch the single row of data
    $row = $result->fetch_assoc();
    $response['base'][] = $row;
} else {
    $response['base'] = array();
}

// Close connection
$conn->close();

// Set header to output JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
