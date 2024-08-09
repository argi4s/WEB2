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
                $taskStmt = $conn->prepare("SELECT productId, quantity, username FROM offers WHERE offerId = ?");
                $taskStmt->bind_param("i", $offerId);
            } elseif ($taskType == 'request') {
                $taskStmt = $conn->prepare("SELECT productId, quantity, username FROM requests WHERE requestId = ?");
                $taskStmt->bind_param("i", $requestId);
            } else {
                throw new Exception("Invalid task type.");
            }

            $taskStmt->execute();
            $taskStmt->bind_result($productId, $taskQuantity, $username);
            $taskStmt->fetch();
            $taskStmt->close();

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

            // Retrieve citizens coordinates
            $citizenStmt = $conn->prepare("SELECT latitude, longitude FROM citizens WHERE username = ?");
            $citizenStmt->bind_param("s", $username);
            $citizenStmt->execute();
            $citizenResult = $citizenStmt->get_result();

            if ($citizenResult->num_rows > 0) {
                $citizenRow = $citizenResult->fetch_assoc();
                $citizenLatitude = $citizenRow['latitude'];
                $citizenLongitude = $citizenRow['longitude'];
            } else {
                echo json_encode(['success' => false, 'message' => 'Citizen location not found']);
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

            // Calculate the distance between rescuer and citizen
            $distance = haversineGreatCircleDistance($rescuerLatitude, $rescuerLongitude, $citizenLatitude, $citizenLongitude);

            if ($distance > 50) {
                echo json_encode(['success' => false, 'message' => 'Rescuer is too far from the citizen']);
                exit;
            }

            // Proceed with the original transaction logic

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
