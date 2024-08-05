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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

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
