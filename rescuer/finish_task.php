<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    if (!empty($id)) {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Get the username from the session
            $rescuerUsername = $_SESSION['username'];

            // Start transaction
            $conn->begin_transaction();

            // Retrieve the taskType and taskIdRef before finishing
            $stmt = $conn->prepare("SELECT taskType, taskIdRef FROM rescuer_tasks WHERE rescuerUsername = ? AND taskId = ?");

            // Bind the parameters
            $stmt->bind_param("si", $rescuerUsername, $id);
            $stmt->execute();
            $stmt->bind_result($taskType, $taskIdRef);
            $stmt->fetch();
            $stmt->close();

            // Update the status in the relevant table
            if ($taskType == 'offer') {
                $updateStmt = $conn->prepare("UPDATE offers SET status = 'finished' WHERE offerId = ?");
            } elseif ($taskType == 'request') {
                $updateStmt = $conn->prepare("UPDATE requests SET status = 'finished' WHERE requestId = ?");
            }

            $updateStmt->bind_param("i", $taskIdRef);

            if ($updateStmt->execute()) {
                echo "Task finished successfully.";
            } else {
                echo "Error finishing task: " . $stmt->error;
            }

            $updateStmt->close();

            // Commit transaction
            $conn->commit();
            $conn->close();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        echo "Invalid offer ID.";
    }
} else {
    echo "Invalid request method.";
}

?>