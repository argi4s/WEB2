<?php
require_once '../session_check.php';
check_login('citizen');



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
if(!isset($_SESSION['username'])) {
    die("User is not logged in.");
}

$loggedInUser = $_SESSION['username'];

// Handle the form submission for offering
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $citizenRequestId = $_POST['citizenRequestId'];
    $citizenUsername = $_POST['citizenUsername'];
    $productName = $_POST['requestProductName'];
    $offerQuantity = $_POST['offerQuantity'];

    // Check if the product exists in the warehouse
    $sqlCheckProduct = "SELECT productId FROM warehouse WHERE productName = '$productName'";
    $resultCheckProduct = $conn->query($sqlCheckProduct);

    if ($resultCheckProduct->num_rows > 0) {
        // Product exists, get the productId
        $rowProduct = $resultCheckProduct->fetch_assoc();
        $productId = $rowProduct['productId'];
    } else {
        // Product doesn't exist, insert it as a dummy product
        $productCategory = 'OTHER'; // You can set a default category or determine it some other way
        $sqlInsertDummyProduct = "INSERT INTO warehouse (productName, productCategory, productQuantity)
                                  VALUES ('$productName', '$productCategory', 0)";
        if ($conn->query($sqlInsertDummyProduct) === TRUE) {
            $productId = $conn->insert_id;
        } else {
            die("Error inserting dummy product: " . $conn->error);
        }
    }

    // Insert the offer into the offers table
    $sqlInsertOffer = "INSERT INTO offers (username, productId, quantity, status)
                       VALUES ('$loggedInUser', $productId, $offerQuantity, 'pending')";

    if ($conn->query($sqlInsertOffer) === TRUE) {
        echo "Offer created successfully!";

        // Update the request's product quantity
        $sqlUpdateRequest = "UPDATE citizen_requests 
                             SET requestProductQuantity = requestProductQuantity - $offerQuantity 
                             WHERE citizenRequestId = $citizenRequestId";
        $conn->query($sqlUpdateRequest);

        // Check if the request's product quantity is now 0 and delete the request if it is
        $sqlCheckQuantity = "SELECT requestProductQuantity FROM citizen_requests WHERE citizenRequestId = $citizenRequestId";
        $resultCheckQuantity = $conn->query($sqlCheckQuantity);
        if ($resultCheckQuantity->num_rows > 0) {
            $rowCheckQuantity = $resultCheckQuantity->fetch_assoc();
            if ($rowCheckQuantity['requestProductQuantity'] <= 0) {
                $sqlDeleteRequest = "DELETE FROM citizen_requests WHERE citizenRequestId = $citizenRequestId";
                $conn->query($sqlDeleteRequest);
            }
        }

    } else {
        echo "Error: " . $conn->error;
    }
}

// Fetch citizen requests
$sqlRequests = "SELECT cr.citizenRequestId, cr.citizenUsername, c.name, c.surname, cr.requestProductName, cr.citizenProductCategory, cr.requestProductQuantity, cr.requestPeopleQuantity 
        FROM citizen_requests cr
        JOIN citizens c ON cr.citizenUsername = c.username";
$resultRequests = $conn->query($sqlRequests);

// Fetch offers made by the logged-in user
$sqlOffers = "SELECT o.offerId, o.quantity, o.createdAt, o.status 
              FROM offers o
              WHERE o.username = '$loggedInUser'";
$resultOffers = $conn->query($sqlOffers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Requests & Your Offers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        .container {
            display: flex;
            justify-content: space-between;
        }
        .left, .right {
            width: 48%;
        }
        .left {
            margin-right: 2%;
        }
        .right {
            margin-left: 2%;
        }
    </style>
</head>
<body>
    <h1>Citizen Requests & Your Offers</h1>
    <div class="container">
        <div class="left">
            <h2>Citizen Requests</h2>
            <?php
            if ($resultRequests->num_rows > 0) {
                while($row = $resultRequests->fetch_assoc()) {
                    echo "<div>";
                    echo "<p><strong>" . $row["name"] . " " . $row["surname"] . "</strong> is requesting <strong>" . $row["requestProductQuantity"] . " " . $row["requestProductName"] . " (" . $row["citizenProductCategory"] . ")</strong> for <strong>" . $row["requestPeopleQuantity"] . " people</strong></p>";
                    echo '<form method="POST" action="">';
                    echo '<input type="hidden" name="citizenRequestId" value="' . $row["citizenRequestId"] . '">';
                    echo '<input type="hidden" name="citizenUsername" value="' . $row["citizenUsername"] . '">';
                    echo '<input type="hidden" name="requestProductName" value="' . $row["requestProductName"] . '">';
                    echo '<label for="quantity">Offer Quantity:</label>';
                    echo '<input type="number" name="offerQuantity" min="1" max="' . $row["requestProductQuantity"] . '" required>';
                    echo '<button type="submit">Offer</button>';
                    echo '</form>';
                    echo "</div><hr>";
                }
            } else {
                echo "<p>No requests available.</p>";
            }
            ?>
        </div>
        
        <div class="right">
            <h2>Your Offers</h2>
            <?php
            if ($resultOffers->num_rows > 0) {
                while($row = $resultOffers->fetch_assoc()) {
                    echo "<div>";
                    echo "<p>You offered <strong>" . $row["quantity"] . " units</strong> on " . $row["createdAt"] . " - Status: " . $row["status"] . "</p>";
                    echo "</div><hr>";
                }
            } else {
                echo "<p>You have not made any offers yet.</p>";
            }
            ?>
        </div>
    </div>

    <a href="citizen_main_page.php" class="button" style="position:absolute;bottom:0%;">Go Back</a>
</body>
</html>
