<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

$input = json_decode(file_get_contents('php://input'), true);
$name = $input['name'];
$quantity = $input['quantity'];
$rescuerUsername = $_SESSION['username'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

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

// Retrieve the product quantity from the warehouse table
$stmt = $conn->prepare("SELECT productQuantity FROM warehouse WHERE productName = ?");
$stmt->bind_param("s", $name);

if (!$stmt->execute()) {
    error_log('Statement execution failed: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $productQuantity = $row['productQuantity'];

    // Check if the requested quantity is available
    if ($quantity > $productQuantity) {
        echo json_encode(['success' => false, 'message' => 'Not enough product quantity available']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update the product quantity in the warehouse table
        $newQuantity = $productQuantity - $quantity;
        $updateStmt = $conn->prepare("UPDATE warehouse SET productQuantity = ? WHERE productName = ?");
        $updateStmt->bind_param("is", $newQuantity, $name);

        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update product quantity: ' . $updateStmt->error);
        }

        // Get the username from the session
        $rescuerUsername = $_SESSION['username'];

        // Check if the item already exists in the onvehicles table
        $checkStmt = $conn->prepare("SELECT productQuantity FROM onvehicles WHERE productName = ? AND rescuerUsername = ?");
        $checkStmt->bind_param("ss", $name, $rescuerUsername);

        if (!$checkStmt->execute()) {
            throw new Exception('Failed to check onvehicles table: ' . $checkStmt->error);
        }

        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            // Item already exists, update the existing row
            $row = $checkResult->fetch_assoc();
            $existingQuantity = $row['productQuantity'];
            $newVehicleQuantity = $existingQuantity + $quantity;

            $updateVehicleStmt = $conn->prepare("UPDATE onvehicles SET productQuantity = ? WHERE productName = ? AND rescuerUsername = ?");
            $updateVehicleStmt->bind_param("iss", $newVehicleQuantity, $name, $rescuerUsername);

            if (!$updateVehicleStmt->execute()) {
                throw new Exception('Failed to update onvehicles: ' . $updateVehicleStmt->error);
            }
        } else {
            // Item does not exist, insert a new row
            $insertStmt = $conn->prepare("INSERT INTO onvehicles (productName, productQuantity, rescuerUsername) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sis", $name, $quantity, $rescuerUsername);

            if (!$insertStmt->execute()) {
                throw new Exception('Failed to insert into onvehicles: ' . $insertStmt->error);
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Product quantity updated and added to onvehicles']);
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Transaction failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
}

$stmt->close();
$conn->close();
?>
