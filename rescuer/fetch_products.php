<?php

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT productName FROM warehouse ORDER BY productName";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['productName']) . "'>" . htmlspecialchars($row['productName']) . "</option>";
    }
} else {
    echo "<option value=''>No products available</option>";
}

$conn->close();
?>
