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

// Ensure the session has the username
if (!isset($_SESSION['username'])) {
    die("User is not logged in.");
}

// Check if making an offer
if (isset($_POST['announcementId']) && isset($_POST['offerQuantity']) && isset($_POST['productId']) && isset($_POST['numberOfPeople'])) {
    $announcementId = $_POST['announcementId'];  // Ensure this ID comes from the form
    $offerQuantity = $_POST['offerQuantity'];
    $productId = $_POST['productId'];            // Ensure this product ID comes from the form
    $numberOfPeople = $_POST['numberOfPeople'];  // Ensure number of people is provided in the form
    $offerUsername = $_SESSION['username'];

    // Insert the offer into the offers table
    $sql = "INSERT INTO offers (username, productId, quantity, numberOfPeople, status, announcementId) 
            VALUES (?, ?, ?, ?, 'pending', ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiii", $offerUsername, $productId, $offerQuantity, $numberOfPeople, $announcementId);

    if ($stmt->execute()) {
        echo "Offer submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} 
// Check if canceling an offer
else if (isset($_POST['cancelOfferId'])) {
    $cancelOfferId = $_POST['cancelOfferId'];
    $cancelUsername = $_SESSION['username'];

    // Check if the offer is pending before canceling
    $sqlCheck = "SELECT status FROM offers WHERE offerId = ? AND username = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("is", $cancelOfferId, $cancelUsername);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $row = $resultCheck->fetch_assoc();
        if ($row['status'] === 'pending') {
            // Proceed to cancel the offer
            $sqlCancel = "DELETE FROM offers WHERE offerId = ? AND username = ?";
            $stmtCancel = $conn->prepare($sqlCancel);
            $stmtCancel->bind_param("is", $cancelOfferId, $cancelUsername);

            if ($stmtCancel->execute()) {
                echo "Offer canceled successfully!";
            } else {
                echo "Error canceling offer: " . $stmtCancel->error;
            }

            $stmtCancel->close();
        } else {
            echo "Cannot cancel offer. Offer is not in 'pending' status.";
        }
    } else {
        echo "Offer not found or you are not authorized to cancel this offer.";
    }

    $stmtCheck->close();
} else {
    echo "Error: Required data is missing.";
}

$conn->close();

// Redirect back to the offers page
header("Location: citizen_announcement_offers.php");
exit();
?>

