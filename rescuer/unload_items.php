<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$rescuerUsername = $_SESSION['username'];

// Retrieve rescuer's coordinates
$rescuerStmt = $conn->prepare("SELECT latitude, longitude FROM rescuers WHERE username = ?");
$rescuerStmt->bind_param("s", $rescuerUsername);
$rescuerStmt->execute();
$rescuerResult = $rescuerStmt->get_result();

if ($rescuerResult->num_rows > 0) {
    $rescuerRow = $rescuerResult->fetch_assoc();
    $rescuerLatitude = $rescuerRow['latitude'];
    $rescuerLongitude = $rescuerRow['longitude'];
} else {
    echo json_encode(['success' => false, 'message' => 'Rescuer location not found']);
    exit;
}

// Retrieve warehouse coordinates
$warehouseStmt = $conn->prepare("SELECT latitude, longitude FROM base LIMIT 1");
$warehouseStmt->execute();
$warehouseResult = $warehouseStmt->get_result();

if ($warehouseResult->num_rows > 0) {
    $warehouseRow = $warehouseResult->fetch_assoc();
    $warehouseLatitude = $warehouseRow['latitude'];
    $warehouseLongitude = $warehouseRow['longitude'];
} else {
    echo json_encode(['success' => false, 'message' => 'Warehouse location not found']);
    exit;
}

function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
    // Convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return $angle * $earthRadius;
}

// Calculate the distance between rescuer and warehouse
$distance = haversineGreatCircleDistance($rescuerLatitude, $rescuerLongitude, $warehouseLatitude, $warehouseLongitude);

if ($distance > 100) {
    echo json_encode(['success' => false, 'message' => 'Rescuer is too far from the warehouse']);
    exit;
}

// Proceed with the original transaction logic

try {
    // Start transaction
    $conn->begin_transaction();

    // Fetch items from onvehicles table for the user
    $stmt = $conn->prepare("SELECT productName, productQuantity FROM onvehicles WHERE rescuerUsername = ?");
    $stmt->bind_param("s", $rescuerUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Process each item
        while ($row = $result->fetch_assoc()) {
            $productName = $row['productName'];
            $productQuantity = $row['productQuantity'];

            // Update the warehouse table
            $updateStmt = $conn->prepare("UPDATE warehouse SET productQuantity = productQuantity + ? WHERE productName = ?");
            $updateStmt->bind_param("is", $productQuantity, $productName);

            if (!$updateStmt->execute()) {
                throw new Exception('Failed to update warehouse: ' . $updateStmt->error);
            }

            $updateStmt->close();
        }

        // Delete items from onvehicles table for the user
        $deleteStmt = $conn->prepare("DELETE FROM onvehicles WHERE rescuerUsername = ?");
        $deleteStmt->bind_param("s", $rescuerUsername);

        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete from onvehicles: ' . $deleteStmt->error);
        }

        $deleteStmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No items found on vehicle']);
        exit;
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Items moved to warehouse successfully']);
} catch (Exception $e) {
    $conn->rollback();
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Transaction failed']);
}

$stmt->close();
$conn->close();
?>