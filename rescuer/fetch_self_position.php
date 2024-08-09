<?php

session_start();

$rescuerUsername = $_SESSION['username'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Fetch the rescuer's position
$stmt = $conn->prepare("SELECT latitude, longitude FROM rescuers WHERE username = ?");
$stmt->bind_param("s", $rescuerUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['latitude' => $row['latitude'], 'longitude' => $row['longitude']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Rescuer position not found']);
}

$stmt->close();
$conn->close();
?>
