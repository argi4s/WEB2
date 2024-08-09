<?php
session_start();

$rescuerUsername = $_SESSION['username'];

$input = json_decode(file_get_contents('php://input'), true);
$latitude = isset($input['latitude']) ? $input['latitude'] : null;
$longitude = isset($input['longitude']) ? $input['longitude'] : null;

if (!$latitude || !$longitude) {
    echo json_encode(['success' => false, 'message' => 'Invalid latitude or longitude']);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Update the rescuer's position
$stmt = $conn->prepare("UPDATE rescuers SET latitude = ?, longitude = ? WHERE username = ?");
$stmt->bind_param("dds", $latitude, $longitude, $rescuerUsername);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Self position updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update self position']);
}

$stmt->close();
$conn->close();
?>
