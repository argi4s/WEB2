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
        $announcementId = $_POST['announcementId'];
        $productName = $_POST['productName'];
        $offerQuantity = $_POST['offerQuantity'];

        // Check if the product exists in the warehouse
        $stmtCheckProduct = $conn->prepare("SELECT productId FROM warehouse WHERE productName = ?");
        $stmtCheckProduct->bind_param('s', $productName);
        $stmtCheckProduct->execute();
        $resultCheckProduct = $stmtCheckProduct->get_result();

        if ($resultCheckProduct->num_rows > 0) {
            // Product exists, get the productId
            $rowProduct = $resultCheckProduct->fetch_assoc();
            $productId = $rowProduct['productId'];
        } else {
            // Product doesn't exist, insert it as a dummy product
            $productCategory = 'OTHER'; // Default category
            $stmtInsertDummyProduct = $conn->prepare("INSERT INTO warehouse (productName, productCategory, productQuantity) VALUES (?, ?, 0)");
            $stmtInsertDummyProduct->bind_param('ss', $productName, $productCategory);
            if ($stmtInsertDummyProduct->execute()) {
                $productId = $conn->insert_id;
            } else {
                die("Error inserting dummy product: " . $conn->error);
            }
        }

        // Insert the offer into the offers table
        $stmtInsertOffer = $conn->prepare("INSERT INTO offers (username, productId, quantity, status, numberOfPeople) VALUES (?, ?, ?, 'pending', 1)");
        $stmtInsertOffer->bind_param('sii', $loggedInUser, $productId, $offerQuantity);
        if ($stmtInsertOffer->execute()) {
            // Update the announcement's product quantity
            $stmtUpdateAnnouncement = $conn->prepare("UPDATE announcements SET quantityRequested = quantityRequested - ? WHERE announcementId = ?");
            $stmtUpdateAnnouncement->bind_param('ii', $offerQuantity, $announcementId);
            if ($stmtUpdateAnnouncement->execute()) {
                // Check if the announcement's quantity is now 0 and hide the announcement if it is
                $stmtCheckQuantity = $conn->prepare("SELECT quantityRequested FROM announcements WHERE announcementId = ?");
                $stmtCheckQuantity->bind_param('i', $announcementId);
                $stmtCheckQuantity->execute();
                $resultCheckQuantity = $stmtCheckQuantity->get_result();
                if ($resultCheckQuantity->num_rows > 0) {
                    $rowCheckQuantity = $resultCheckQuantity->fetch_assoc();
                    if ($rowCheckQuantity['quantityRequested'] <= 0) {
                        // Hide the announcement by setting a flag
                        $stmtHideAnnouncement = $conn->prepare("UPDATE announcements SET status = 'fulfilled' WHERE announcementId = ?");
                        $stmtHideAnnouncement->bind_param('i', $announcementId);
                        $stmtHideAnnouncement->execute();
                    }
                }

                echo "Offer created successfully!";
            } else {
                echo "Error updating announcement: " . $conn->error;
            }
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif (isset($_POST['cancelOfferId'])) {
        // Handle canceling
        $cancelOfferId = $_POST['cancelOfferId'];

        // Check if the offer is pending
        $stmtCheckStatus = $conn->prepare("SELECT status, productId, quantity FROM offers WHERE offerId = ? AND username = ?");
        $stmtCheckStatus->bind_param('is', $cancelOfferId, $loggedInUser);
        $stmtCheckStatus->execute();
        $resultCheckStatus = $stmtCheckStatus->get_result();
        
        if ($resultCheckStatus->num_rows > 0) {
            $rowCheckStatus = $resultCheckStatus->fetch_assoc();
            if ($rowCheckStatus['status'] === 'pending') {
                // Update the offer status to 'cancelled'
                $stmtCancelOffer = $conn->prepare("UPDATE offers SET status = 'cancelled' WHERE offerId = ? AND username = ?");
                $stmtCancelOffer->bind_param('is', $cancelOfferId, $loggedInUser);
                if ($stmtCancelOffer->execute()) {
                    $productId = $rowCheckStatus['productId'];
                    $offerQuantity = $rowCheckStatus['quantity'];

                    // Restore the original announcement quantity
                    $stmtRestoreAnnouncement = $conn->prepare("UPDATE announcements SET quantityRequested = quantityRequested + ? WHERE announcementId = (SELECT announcementId FROM announcements WHERE productId = ? LIMIT 1)");
                    $stmtRestoreAnnouncement->bind_param('ii', $offerQuantity, $productId);
                    $stmtRestoreAnnouncement->execute();
                    
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

// Fetch announcements that are still active
$stmtAnnouncements = $conn->prepare("SELECT a.announcementId, c.name, c.surname, w.productName AS productName, r.quantity AS quantityRequested
                                      FROM announcements a
                                      JOIN requests r ON a.announcements_requestId = r.requestId
                                      JOIN citizens c ON r.username = c.username
                                      JOIN warehouse w ON r.productId = w.productId
                                      WHERE r.quantity > 0");
$stmtAnnouncements->execute();
$resultAnnouncements = $stmtAnnouncements->get_result();

// Fetch pending offers made by the logged-in user
$stmtOffers = $conn->prepare("SELECT o.offerId, o.quantity, o.createdAt, o.status, w.productName
                              FROM offers o
                              JOIN warehouse w ON o.productId = w.productId
                              WHERE o.username = ? AND o.status = 'pending'");
$stmtOffers->bind_param('s', $loggedInUser);
$stmtOffers->execute();
$resultOffers = $stmtOffers->get_result();

// Fetch previous offers (with status 'finished' or 'taken') made by the logged-in user
$stmtPreviousOffers = $conn->prepare("SELECT o.offerId, o.quantity, o.createdAt, o.status, o.acceptDate, o.completeDate, w.productName
                                      FROM offers o
                                      JOIN warehouse w ON o.productId = w.productId
                                      WHERE o.username = ? AND (o.status = 'finished' OR o.status = 'taken')");
$stmtPreviousOffers->bind_param('s', $loggedInUser);
$stmtPreviousOffers->execute();
$resultPreviousOffers = $stmtPreviousOffers->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements & Your Offers</title>
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
        .announcements-container {
            max-height: 600px; /* Adjust the height as needed */
            overflow-y: auto; /* Enables vertical scrolling */
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .announcement-item {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .announcement-item:last-child {
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
            width: 100%;
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
    <h1>Announcements & Your Offers</h1>
    <div class="container">
        <div class="left">
            <h2>Announcements</h2>
            <div class="announcements-container">
                <?php
                if ($resultAnnouncements->num_rows > 0) {
                    while ($row = $resultAnnouncements->fetch_assoc()) {
                        echo '<div class="announcement-item">';
                        echo "<p><strong>" . htmlspecialchars($row["name"]) . " " . htmlspecialchars($row["surname"]) . "</strong> has requested <strong>" 
                                            . htmlspecialchars($row["quantityRequested"]) . " " . htmlspecialchars($row["productName"]) . "</strong></p>";
                        echo '<form method="POST" action="">';
                        echo '<input type="hidden" name="announcementId" value="' . htmlspecialchars($row["announcementId"]) . '">';
                        echo '<input type="hidden" name="productName" value="' . htmlspecialchars($row["productName"]) . '">';
                        echo '<label for="quantity">Offer Quantity:</label>';
                        echo '<input type="number" name="offerQuantity" min="1" max="' . htmlspecialchars($row["quantityRequested"]) . '" required>';
                        echo '<button type="submit">Offer</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No announcements available.</p>";
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
                    echo "<p>You offered <strong>" . htmlspecialchars($row["quantity"]) . " units of " . htmlspecialchars($row["productName"]) . "</strong> on " 
                         . htmlspecialchars($row["createdAt"]) . " - Status: " . htmlspecialchars($row["status"]) . "</p>";
                    if ($row["status"] === 'pending') {
                        echo '<form method="POST" action="">';
                        echo '<input type="hidden" name="cancelOfferId" value="' . htmlspecialchars($row["offerId"]) . '">';
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
                        echo "<tr><td colspan='6'>No previous offers available.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="citizen_main_page.php" class="back-button">Back</a>
</body>
</html>

<?php
$conn->close();
?>
