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
            $productCategory = 'OTHER'; // Default category
            $sqlInsertDummyProduct = "INSERT INTO warehouse (productName, productCategory, productQuantity)
                                      VALUES ('$productName', '$productCategory', 0)";
            if ($conn->query($sqlInsertDummyProduct) === TRUE) {
                $productId = $conn->insert_id;
            } else {
                die("Error inserting dummy product: " . $conn->error);
            }
        }

        // Insert the offer into the offers table
        $sqlInsertOffer = "INSERT INTO offers (username, productId, quantity, status, numberOfPeople)
                           VALUES ('$loggedInUser', $productId, $offerQuantity, 'pending', 1)";

        if ($conn->query($sqlInsertOffer) === TRUE) {
            // Update the request's product quantity
            $sqlUpdateRequest = "UPDATE requests 
                                 SET quantity = quantity - $offerQuantity 
                                 WHERE requestId = $requestId";
            $conn->query($sqlUpdateRequest);

            // Check if the request's quantity is now 0 and hide the request if it is
            $sqlCheckQuantity = "SELECT quantity FROM requests WHERE requestId = $requestId";
            $resultCheckQuantity = $conn->query($sqlCheckQuantity);
            if ($resultCheckQuantity->num_rows > 0) {
                $rowCheckQuantity = $resultCheckQuantity->fetch_assoc();
                if ($rowCheckQuantity['quantity'] <= 0) {
                    // Hide the request by setting a flag
                    $sqlHideRequest = "UPDATE requests SET isHidden = 1 WHERE requestId = $requestId";
                    $conn->query($sqlHideRequest);
                }
            }

            echo "Offer created successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif (isset($_POST['cancelOfferId'])) {
        // Handle canceling
        $cancelOfferId = $_POST['cancelOfferId'];

        // Check if the offer is pending
        $sqlCheckStatus = "SELECT status FROM offers WHERE offerId = $cancelOfferId AND username = '$loggedInUser'";
        $resultCheckStatus = $conn->query($sqlCheckStatus);
        
        if ($resultCheckStatus->num_rows > 0) {
            $rowCheckStatus = $resultCheckStatus->fetch_assoc();
            if ($rowCheckStatus['status'] === 'pending') {
                // Cancel the offer
                $sqlCancelOffer = "DELETE FROM offers WHERE offerId = $cancelOfferId AND username = '$loggedInUser'";
                if ($conn->query($sqlCancelOffer) === TRUE) {
                    // Restore the original request quantity
                    $sqlGetOffer = "SELECT productId, quantity FROM offers WHERE offerId = $cancelOfferId";
                    $resultGetOffer = $conn->query($sqlGetOffer);
                    if ($resultGetOffer->num_rows > 0) {
                        $offer = $resultGetOffer->fetch_assoc();
                        $offerQuantity = $offer['quantity'];
                        $productId = $offer['productId'];
                        $sqlUpdateRequest = "UPDATE requests SET quantity = quantity + $offerQuantity WHERE productId = $productId";
                        $conn->query($sqlUpdateRequest);
                    }

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

// Fetch requests that are not hidden
$sqlRequests = "SELECT r.requestId, r.username, c.name, c.surname, w.productName AS requestProductName, 
                r.quantity, r.numberOfPeople 
        FROM requests r
        JOIN citizens c ON r.username = c.username
        JOIN warehouse w ON r.productId = w.productId
        WHERE r.isHidden = 0";
$resultRequests = $conn->query($sqlRequests);

// Fetch offers made by the logged-in user
$sqlOffers = "SELECT o.offerId, o.quantity, o.createdAt, o.status, w.productName
              FROM offers o
              JOIN warehouse w ON o.productId = w.productId
              WHERE o.username = '$loggedInUser'";
$resultOffers = $conn->query($sqlOffers);

// Fetch previous offers (with status 'finished' or 'taken') made by the logged-in user
$sqlPreviousOffers = "SELECT o.offerId, o.quantity, o.createdAt, o.status, o.acceptDate, o.completeDate, w.productName
                      FROM offers o
                      JOIN warehouse w ON o.productId = w.productId
                      WHERE o.username = '$loggedInUser' AND (o.status = 'finished' OR o.status = 'taken')";
$resultPreviousOffers = $conn->query($sqlPreviousOffers);

// Delete requests that have offers with status other than 'pending'
$sqlDeleteRequests = "DELETE r FROM requests r
                      JOIN offers o ON r.productId = o.productId
                      WHERE o.status != 'pending'";
$conn->query($sqlDeleteRequests);
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
        .requests-container {
            max-height: 600px; /* Adjust the height as needed */
            overflow-y: auto; /* Enables vertical scrolling */
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .request-item {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .request-item:last-child {
            border-bottom: none;
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
        table {
            width: 150%; /* Increase table width to 150% */
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Requests & Your Offers</h1>
    <div class="container">
        <div class="left">
            <h2>Requests</h2>
            <div class="requests-container">
                <?php
                if ($resultRequests->num_rows > 0) {
                    while ($row = $resultRequests->fetch_assoc()) {
                        echo '<div class="request-item">';
                        echo "<p><strong>" . $row["name"] . " " . $row["surname"] . "</strong> has requested <strong>" 
                                            . $row["quantity"] . " " . $row["requestProductName"] . "</strong> for <strong>" . $row["numberOfPeople"] . " people</strong></p>";
                        echo '<form method="POST" action="">';
                        echo '<input type="hidden" name="requestId" value="' . $row["requestId"] . '">';
                        echo '<input type="hidden" name="requestProductName" value="' . $row["requestProductName"] . '">';
                        echo '<label for="quantity">Offer Quantity:</label>';
                        echo '<input type="number" name="offerQuantity" min="1" max="' . $row["quantity"] . '" required>';
                        echo '<button type="submit">Offer</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No requests available.</p>";
                }
                ?>
            </div>
        </div>
        
        <div class="right">
            <h2>Your Offers</h2>
            <?php
            if ($resultOffers->num_rows > 0) {
                while ($row = $resultOffers->fetch_assoc()) {
                    echo "<div>";
                    echo "<p>You offered <strong>" . $row["quantity"] . " units of " . $row["productName"] . "</strong> on " 
                         . $row["createdAt"] . " - Status: " . $row["status"] . "</p>";
                    if ($row["status"] === 'pending') {
                        echo '<form method="POST" action="">';
                        echo '<input type="hidden" name="cancelOfferId" value="' . $row["offerId"] . '">';
                        echo '<button type="submit">Cancel Offer</button>';
                        echo '</form>';
                    }
                    echo "</div><hr>";
                }
            } else {
                echo "<p>You have not made any offers yet.</p>";
            }
            ?>
            
            <h2>Previous Offers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Accept Date</th>
                        <th>Complete Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($resultPreviousOffers->num_rows > 0) {
                        while ($row = $resultPreviousOffers->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row["productName"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["quantity"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["createdAt"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["status"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["acceptDate"]) . '</td>';
                            echo '<td>' . htmlspecialchars($row["completeDate"]) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="6">No previous offers available.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="citizen_main_page.php" class="back-button">Go Back</a>
</body>
</html>

