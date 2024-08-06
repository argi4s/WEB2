<?php

session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskId = $_POST['id'];

    if (!empty($taskId)) {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Get the username from the session
            $rescuerUsername = $_SESSION['username'];

            // Ensure the rescuer exists in the rescuers table
            $rescuerStmt = $conn->prepare("SELECT username FROM rescuers WHERE username = ?");
            $rescuerStmt->bind_param("s", $rescuerUsername);
            $rescuerStmt->execute();
            $rescuerStmt->store_result();
            if ($rescuerStmt->num_rows == 0) {
                throw new Exception("Rescuer does not exist.");
            }
            $rescuerStmt->close();

            // Start transaction
            $conn->begin_transaction();

            // Retrieve the taskType, requestId and offerId before finishing
            $stmt = $conn->prepare("SELECT taskType, requestId, offerId FROM rescuer_tasks WHERE rescuerUsername = ? AND taskId = ?");
            $stmt->bind_param("si", $rescuerUsername, $taskId);
            $stmt->execute();
            $stmt->bind_result($taskType, $requestId, $offerId);
            $stmt->fetch();
            $stmt->close();

            // Determine the required product and quantity from the relevant table
            if ($taskType == 'offer') {
                $taskStmt = $conn->prepare("SELECT productId, quantity FROM offers WHERE offerId = ?");
                $taskStmt->bind_param("i", $offerId);
            } elseif ($taskType == 'request') {
                $taskStmt = $conn->prepare("SELECT productId, quantity FROM requests WHERE requestId = ?");
                $taskStmt->bind_param("i", $requestId);
            } else {
                throw new Exception("Invalid task type.");
            }
            
            $taskStmt->execute();
            $taskStmt->bind_result($productId, $taskQuantity);
            $taskStmt->fetch();
            $taskStmt->close();

            // Fetch the product name from the warehouse table using productId
            $productStmt = $conn->prepare("SELECT productName FROM warehouse WHERE productId = ?");
            $productStmt->bind_param("i", $productId);
            $productStmt->execute();
            $productStmt->bind_result($productName);
            $productStmt->fetch();
            $productStmt->close();

            // Check if rescuer has the required items on their vehicle
            $vehicleStmt = $conn->prepare("SELECT productQuantity FROM onvehicles WHERE rescuerUsername = ? AND productName = ?");
            $vehicleStmt->bind_param("ss", $rescuerUsername, $productName);
            $vehicleStmt->execute();
            $vehicleStmt->bind_result($currentQuantity);
            $vehicleExists = $vehicleStmt->fetch();
            $vehicleStmt->close();

            if ($taskType == 'request') {
                if (!$vehicleExists || $currentQuantity < $taskQuantity) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient items on the vehicle']);
                    $conn->rollback();
                    $conn->close();
                    exit;
                }

                // Update the quantity on the vehicle
                $newQuantity = $currentQuantity - $taskQuantity;
                if ($vehicleExists) {
                    $updateVehicleStmt = $conn->prepare("UPDATE onvehicles SET productQuantity = ? WHERE rescuerUsername = ? AND productName = ?");
                    $updateVehicleStmt->bind_param("iss", $newQuantity, $rescuerUsername, $productName);
                } else {
                    $updateVehicleStmt = $conn->prepare("INSERT INTO onvehicles (productName, productQuantity, rescuerUsername) VALUES (?, ?, ?)");
                    $updateVehicleStmt->bind_param("sis", $productName, $newQuantity, $rescuerUsername);
                }
                $updateVehicleStmt->execute();
                $updateVehicleStmt->close();

                // Update the task status
                $updateTaskStmt = $conn->prepare("UPDATE requests SET status = 'finished' WHERE requestId = ?");
                $updateTaskStmt->bind_param("i", $requestId);
            } elseif ($taskType == 'offer') {
                // Update the quantity on the vehicle
                $newQuantity = $vehicleExists ? $currentQuantity + $taskQuantity : $taskQuantity;
                if ($vehicleExists) {
                    $updateVehicleStmt = $conn->prepare("UPDATE onvehicles SET productQuantity = ? WHERE rescuerUsername = ? AND productName = ?");
                    $updateVehicleStmt->bind_param("iss", $newQuantity, $rescuerUsername, $productName);
                } else {
                    $updateVehicleStmt = $conn->prepare("INSERT INTO onvehicles (productName, productQuantity, rescuerUsername) VALUES (?, ?, ?)");
                    $updateVehicleStmt->bind_param("sis", $productName, $newQuantity, $rescuerUsername);
                }
                $updateVehicleStmt->execute();
                $updateVehicleStmt->close();

                // Update the task status
                $updateTaskStmt = $conn->prepare("UPDATE offers SET status = 'finished' WHERE offerId = ?");
                $updateTaskStmt->bind_param("i", $offerId);
            } else {
                throw new Exception("Invalid task type.");
            }

            if ($updateTaskStmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Task finished successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error finishing task: ' . $updateTaskStmt->error]);
            }
            $updateTaskStmt->close();

            // Commit transaction
            $conn->commit();
            $conn->close();
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid task ID.']);
}
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
