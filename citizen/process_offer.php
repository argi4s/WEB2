<?php
session_start();

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$citizenRequestId = $_POST['citizenRequestId'];
$citizenUsername = $_POST['citizenUsername'];
$requestProductName = $_POST['requestProductName'];
$offerQuantity = $_POST['offerQuantity'];
$offerUsername = $_SESSION['username'];  // Assuming the logged-in user is making the offer

// Insert the offer into the offers table
$sql = "INSERT INTO offers (username, productId, quantity, status) 
        SELECT '$offerUsername', productId, $offerQuantity, 'pending' 
        FROM warehouse 
        WHERE productName = '$requestProductName'";

if ($conn->query($sql) === TRUE) {
    // Update the citizen request to reflect the offer
    $remainingQuantity = "(SELECT requestProductQuantity FROM citizen_requests WHERE citizenRequestId = $citizenRequestId) - $offerQuantity";
    $conn->query("UPDATE citizen_requests SET requestProductQuantity = $remainingQuantity WHERE citizenRequestId = $citizenRequestId");

    echo "Offer submitted successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();

// Redirect back to the requests page or another page
header("Location: citizen_announcement_offers.php");
exit();
?>
