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

// Query to fetch latitude and longitude
$sql = "SELECT latitude, longitude FROM base LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $latitude = $row["latitude"];
        $longitude = $row["longitude"];
        echo json_encode(array("latitude" => $latitude, "longitude" => $longitude));
    }
} else {
    echo json_encode(array("latitude" => 0, "longitude" => 0));
}
$conn->close();
?>
