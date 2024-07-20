<?php

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

            $stmt = $conn->prepare("UPDATE requests SET status = 'taken' WHERE requestId = ?");
            $stmt->bind_param("i", $requestId);

            if ($stmt->execute()) {
                echo "Request status updated successfully.";
            } else {
                echo "Error updating request status: " . $stmt->error;
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