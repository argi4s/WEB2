<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $offerId = $_POST['offerId'];

    if (!empty($offerId)) {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Get the username from the session
            $rescuerUsername = $_SESSION['username'];

            // Check the number of pending tasks
            $pendingTasksStmt = $conn->prepare("
                SELECT COUNT(*) as pendingTasksCount 
                FROM rescuer_tasks rt
                LEFT JOIN requests r ON rt.taskType = 'request' AND rt.requestId = r.requestId
                LEFT JOIN offers o ON rt.taskType = 'offer' AND rt.offerId = o.offerId
                WHERE rt.rescuerUsername = ? AND COALESCE(r.status, o.status) = 'taken'");
            $pendingTasksStmt->bind_param("s", $rescuerUsername);
            $pendingTasksStmt->execute();
            $pendingTasksResult = $pendingTasksStmt->get_result();
            $pendingTasksRow = $pendingTasksResult->fetch_assoc();
            $pendingTasksCount = $pendingTasksRow['pendingTasksCount'];

            if ($pendingTasksCount >= 4) {
                echo "Error: You already have 4 active tasks.";
                $pendingTasksStmt->close();
                $conn->close();
                exit;
            }

            $pendingTasksStmt->close();

            // Prepare statement to insert into rescuer_tasks
            $stmt = $conn->prepare("INSERT INTO rescuer_tasks (rescuerUsername, taskType, offerId) VALUES (?, 'offer', ?)");
            $stmt->bind_param("si", $rescuerUsername, $offerId);

            if ($stmt->execute()) {
                echo "Offer taken successfully.";
            } else {
                echo "Error taking offer: " . $stmt->error;
            }

            $stmt->close();
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