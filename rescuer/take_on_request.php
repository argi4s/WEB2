<?php

session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestId = $_POST['requestId'];

    if (!empty($requestId)) {
        try {
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }

            // Get the username from the session
            $rescuerUsername = $_SESSION['username'];

            // Prepare statement to insert into rescuer_tasks
            $stmt = $conn->prepare("INSERT INTO rescuer_tasks (rescuerUsername, taskType, taskIdRef) VALUES (?, 'request', ?)");
            $stmt->bind_param("si", $rescuerUsername, $requestId);

            if ($stmt->execute()) {
                echo "Request taken successfully.";
            } else {
                echo "Error taking request: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        echo "Invalid request ID.";
    }
} else {
    echo "Invalid request method.";
}

?>