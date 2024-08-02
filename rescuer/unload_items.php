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