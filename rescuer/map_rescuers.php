<?php

session_start();

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

// Get the username from the session
$rescuerUsername = $_SESSION['username'];

// Fetch rescuers
$sql = "SELECT username, latitude, longitude FROM rescuers WHERE username != ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rescuerUsername);
$stmt->execute();
$result = $stmt->get_result();

$features = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $features[] = [
            "type" => "Feature",
            "properties" => [
                "username" => $row["username"]
            ],
            "geometry" => [
                "type" => "Point",
                "coordinates" => [(float)$row["longitude"], (float)$row["latitude"]]
            ]
        ];
    }
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson);

$conn->close();
?>
