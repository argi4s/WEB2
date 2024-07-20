<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$latitude = filter_input(INPUT_POST, 'latitude', FILTER_VALIDATE_FLOAT);
$longitude = filter_input(INPUT_POST, 'longitude', FILTER_VALIDATE_FLOAT);

if ($latitude !== false && $longitude !== false) {
    $sql = "UPDATE base SET latitude = ?, longitude = ? WHERE id = 1"; // Assuming there's only one row in the base table
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('dd', $latitude, $longitude);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update coordinates."]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid coordinates."]);
}

$conn->close();
?>
