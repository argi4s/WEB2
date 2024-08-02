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

            // Prepare statement to delete from rescuer_tasks
            $stmt = $conn->prepare("DELETE FROM rescuer_tasks WHERE rescuerUsername = ? AND taskId = ?");
            $stmt->bind_param("si", $rescuerUsername, $id);

            if ($stmt->execute()) {
                echo "Task canceled successfully.";
            } else {
                echo "Error canceling task: " . $stmt->error;
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