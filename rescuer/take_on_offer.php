<?php

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

            $stmt = $conn->prepare("UPDATE offers SET status = 'taken' WHERE offerId = ?");
            $stmt->bind_param("i", $offerId);

            if ($stmt->execute()) {
                echo "Offer status updated successfully.";
            } else {
                echo "Error updating offer status: " . $stmt->error;
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