<?php
$host = "localhost";
$dbname = "vasi";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$startDate = $_POST['startDate'];
$endDate = $_POST['endDate'];

$response = []; // For pending/completed requests/offers

// Fetch new requests
$sql = "SELECT COUNT(*) AS count FROM requests WHERE createdAt BETWEEN ? AND ?";                    // Dialegei requests sto xroniko plaisio pou orizei sto interface o xrhsths
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$response['newRequests'] = $result->fetch_assoc()['count'];

// Fetch new offers
$sql = "SELECT COUNT(*) AS count FROM offers WHERE createdAt BETWEEN ? AND ?";                      // Dialegei offers sto xroniko plaisio pou orizei sto interface o xrhsths
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$response['newOffers'] = $result->fetch_assoc()['count'];

// Fetch completed requests
$sql = "SELECT COUNT(*) AS count FROM requests WHERE status = 'finished' AND createdAt BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$response['completedRequests'] = $result->fetch_assoc()['count'];

// Fetch completed offers
$sql = "SELECT COUNT(*) AS count FROM offers WHERE status = 'finished' AND createdAt BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$response['completedOffers'] = $result->fetch_assoc()['count'];

echo json_encode($response);

$conn->close();
?>
