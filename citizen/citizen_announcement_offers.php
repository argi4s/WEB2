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
if (!isset($_SESSION['username'])) {
    die("User is not logged in.");
}

$loggedInUser = $_SESSION['username'];

// Handle the form submission for offering
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the form is for offering or canceling
    if (isset($_POST['offerQuantity'])) {
        // Handle offering
        $requestId = $_POST['requestId'];
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

        // Insert the offer into the requests table
        $sqlInsertOffer = "INSERT INTO requests (username, productId, quantity, status, requestProductName, citizenProductCategory, numberOfPeople)
                           VALUES ('$loggedInUser', $productId, $offerQuantity, 'pending', '$productName', 'OTHER', 1)";

        if ($conn->query($sqlInsertOffer) === TRUE) {
            echo "Offer created successfully!";

            // Update the request's product quantity
            $sqlUpdateRequest = "UPDATE requests 
                                 SET quantity = quantity - $offerQuantity 
                                 WHERE requestId = $requestId";
            $conn->query($sqlUpdateRequest);

            // Check if the request's quantity is now 0 and delete the request if it is
            $sqlCheckQuantity = "SELECT quantity FROM requests WHERE requestId = $requestId";
            $resultCheckQuantity = $conn->query($sqlCheckQuantity);
            if ($resultCheckQuantity->num_rows > 0) {
                $rowCheckQuantity = $resultCheckQuantity->fetch_assoc();
                if ($rowCheckQuantity['quantity'] <= 0) {
                    $sqlDeleteRequest = "DELETE FROM requests WHERE requestId = $requestId";
                    $conn->query($sqlDeleteRequest);
                }
            }

        } else {
            echo "Error: " . $conn->error;
        }
    } elseif (isset($_POST['cancelOfferId'])) {
        // Handle canceling
        $cancelOfferId = $_POST['cancelOfferId'];

        // Check if the offer is pending
        $sqlCheckStatus = "SELECT status FROM requests WHERE requestId = $cancelOfferId AND username = '$loggedInUser'";
        $resultCheckStatus = $conn->query($sqlCheckStatus);
        
        if ($resultCheckStatus->num_rows > 0) {
            $rowCheckStatus = $resultCheckStatus->fetch_assoc();
            if ($rowCheckStatus['status'] === 'pending') {
                // Cancel the offer
                $sqlCancelOffer = "DELETE FROM requests WHERE requestId = $cancelOfferId AND username = '$loggedInUser'";
                if ($conn->query($sqlCancelOffer) === TRUE) {
                    echo "Offer canceled successfully!";
                } else {
                    echo "Error canceling offer: " . $conn->error;
                }
            } else {
                echo "Cannot cancel offer. Offer is not in 'pending' status.";
            }
        } else {
            echo "Offer not found or you are not authorized to cancel this offer.";
        }
    }
}

// Fetch requests
$sqlRequests = "SELECT r.requestId, r.username, c.name, c.surname, r.requestProductName, 
                r.citizenProductCategory, r.quantity, r.numberOfPeople 
        FROM requests r
        JOIN citizens c ON r.username = c.username";
$resultRequests = $conn->query($sqlRequests);

// Fetch offers made by the logged-in user
$sqlOffers = "SELECT r.requestId, r.quantity, r.createdAt, r.status 
              FROM requests r
              WHERE r.username = '$loggedInUser'";
$resultOffers = $conn->query($sqlOffers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests & Your Offers</title>
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
        .back-button {
            position: fixed;
            bottom: 10px;
            left: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Requests & Your Offers</h1>
    <div class="container">
        <div class="left">
            <h2>Requests</h2>
            <?php
            if ($resultRequests->num_rows > 0) {
                while ($row = $resultRequests->fetch_assoc()) {
                    echo "<div>";
                    echo "<p><strong>" . $row["name"] . " " . $row["surname"] . "</strong> has requested <strong>" 
                                        . $row["quantity"] . " " . $row["requestProductName"] . " (" 
                                        . $row["citizenProductCategory"] . ")</strong> for <strong>" . $row["numberOfPeople"] . " people</strong></p>";
                    echo '<form method="POST" action="">';
                    echo '<input type="hidden" name="requestId" value="' . $row["requestId"] . '">';
                    echo '<input type="hidden" name="requestProductName" value="' . $row["requestProductName"] . '">';
                    echo '<label for="quantity">Offer Quantity:</label>';
                    echo '<input type="number" name="offerQuantity" min="1" max="' . $row["quantity"] . '" required>';
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
                while ($row = $resultOffers->fetch_assoc()) {
                    echo "<div>";
                    echo "<p>You offered <strong>" . $row["quantity"] . " units</strong> on " . $row["createdAt"] . " - Status: " . $row["status"] . "</p>";
                    if ($row["status"] === 'pending') {
                        echo '<form method="POST" action="">';
                        echo '<input type="hidden" name="cancelOfferId" value="' . $row["requestId"] . '">';
                        echo '<button type="submit">Cancel Offer</button>';
                        echo '</form>';
                    }
                    echo "</div><hr>";
                }
            } else {
                echo "<p>You have not made any offers yet.</p>";
            }
            ?>
        </div>
    </div>

    <a href="citizen_main_page.php" class="back-button">Go Back</a>
</body>
</html>
