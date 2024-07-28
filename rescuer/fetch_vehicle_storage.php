<?php

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from the session
$rescuerUsername = $_SESSION['username'];

// Prepare the SQL statement
$sql = "SELECT productName, productQuantity FROM onvehicles WHERE rescuerUsername = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rescuerUsername);
$stmt->execute();
$result = $stmt->get_result();

// Check if any records were found
if ($result->num_rows > 0) {
    // Output the data for each row
    while ($row = $result->fetch_assoc()) {
        echo '<div class="list-item">' . htmlspecialchars($row["productQuantity"]) . ' - ' . htmlspecialchars($row["productName"]) . '</div>';
    }
} else {
    echo '<div class="list-item">No items in your storage</div>';
}

// Close the statement and connection
$stmt->close();
$conn->close();