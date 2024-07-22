<?php
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

// Fetch rescuers
$rescuers_sql = "SELECT latitude, longitude FROM rescuers";
$rescuers_result = $conn->query($rescuers_sql);

$rescuers = [];
if ($rescuers_result->num_rows > 0) {
    while ($row = $rescuers_result->fetch_assoc()) {
        $rescuers[] = $row;
    }
}

// Fetch citizens
$citizens_sql = "SELECT latitude, longitude FROM citizens";
$citizens_result = $conn->query($citizens_sql);

$citizens = [];
if ($citizens_result->num_rows > 0) {
    while ($row = $citizens_result->fetch_assoc()) {
        $citizens[] = $row;
    }
}

echo json_encode(['rescuers' => $rescuers, 'citizens' => $citizens]);

$conn->close();
?>
