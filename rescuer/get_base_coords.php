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
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch the base coordinates
$sql = "SELECT latitude, longitude FROM base LIMIT 1";
$result = $conn->query($sql);

$response = array();

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
