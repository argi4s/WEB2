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

// Handle the form submission for offerin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['offerQuantity'])) {
        // Handle offering
        $announcementId = $_POST['announcementId'];
        $offerQuantity = $_POST['offerQuantity'];

        // Fetch the productId associated with the announcement
        $stmtFetchProduct = $conn->prepare("SELECT r.productId FROM announcements a
                                             JOIN requests r ON a.announcements_requestId = r.requestId
                                             WHERE a.announcementId = ?");
        $stmtFetchProduct->bind_param('i', $announcementId);
        $stmtFetchProduct->execute();
        $resultFetchProduct = $stmtFetchProduct->get_result();

        if ($resultFetchProduct->num_rows > 0) {
            $rowFetchProduct = $resultFetchProduct->fetch_assoc();
            $productId = $rowFetchProduct['productId'];

            // Insert the offer into the offers table
            $stmtInsertOffer = $conn->prepare("INSERT INTO offers (username, productId, quantity, status) VALUES (?, ?, ?, 'pending')");
            $stmtInsertOffer->bind_param('sii', $loggedInUser, $productId, $offerQuantity);
            if ($stmtInsertOffer->execute()) {
                // Update the announcement's quantity if necessary (handle your logic)
                echo "Offer created successfully!";
            } else {
                echo "Error: " . $stmtInsertOffer->error;
            }
        } else {
            echo "Announcement not found.";
        }
    } elseif (isset($_POST['cancelOfferId'])) {
        // Handle canceling
        $cancelOfferId = $_POST['cancelOfferId'];

        // Check if the offer is pending
        $stmtCheckStatus = $conn->prepare("SELECT status FROM offers WHERE offerId = ? AND username = ?");
        $stmtCheckStatus->bind_param('is', $cancelOfferId, $loggedInUser);
        $stmtCheckStatus->execute();
        $resultCheckStatus = $stmtCheckStatus->get_result();

        if ($resultCheckStatus->num_rows > 0) {
            $rowCheckStatus = $resultCheckStatus->fetch_assoc();
            if ($rowCheckStatus['status'] === 'pending') {
                // Delete the offer
                $stmtCancelOffer = $conn->prepare("DELETE FROM offers WHERE offerId = ? AND username = ?");
                $stmtCancelOffer->bind_param('is', $cancelOfferId, $loggedInUser);
                if ($stmtCancelOffer->execute()) {
                    echo "Offer canceled successfully!";
                } else {
                    echo "Error canceling offer: " . $stmtCancelOffer->error;
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
$stmtAnnouncements = $conn->prepare("SELECT a.announcementId, w.productName AS productName, r.quantity AS quantityRequested
                                      FROM announcements a
                                      JOIN requests r ON a.announcements_requestId = r.requestId
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
                        echo "<p>Requested <strong>" . htmlspecialchars($row["quantityRequested"]) . " units of " . htmlspecialchars($row["productName"]) . "</strong></p>";
                        echo '<form method="POST" action="process_offer.php">';
                        echo '<input type="hidden" name="announcementId" value="' . htmlspecialchars($row["announcementId"]) . '">';
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
            <h2>Your Pending Offers</h2>
            <?php if ($resultOffers->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Offer ID</th>
                        <th>Quantity</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $resultOffers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["offerId"]); ?></td>
                            <td><?php echo htmlspecialchars($row["quantity"]); ?></td>
                            <td><?php echo htmlspecialchars($row["createdAt"]); ?></td>
                            <td><?php echo htmlspecialchars($row["status"]); ?></td>
                            <td>
                            <form method="POST" action="process_offer.php">
    <input type="hidden" name="announcementId" value="<?php echo $row['announcementId']; ?>">
    <input type="hidden" name="productId" value="<?php echo $row['productId']; ?>">
    
    <!-- Number of people and offer quantity should be provided in the form -->
    <label for="offerQuantity">Quantity:</label>
    <input type="number" name="offerQuantity" required>
    
    <label for="numberOfPeople">Number of People:</label>
    <input type="number" name="numberOfPeople" required>
    
    <button type="submit">Make Offer</button>
</form>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No pending offers.</p>
            <?php endif; ?>

            <h2>Your Previous Offers</h2>
            <?php if ($resultPreviousOffers->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Offer ID</th>
                        <th>Quantity</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Accept Date</th>
                        <th>Complete Date</th>
                    </tr>
                    <?php while ($row = $resultPreviousOffers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row["offerId"]); ?></td>
                            <td><?php echo htmlspecialchars($row["quantity"]); ?></td>
                            <td><?php echo htmlspecialchars($row["createdAt"]); ?></td>
                            <td><?php echo htmlspecialchars($row["status"]); ?></td>
                            <td><?php echo htmlspecialchars($row["acceptDate"]); ?></td>
                            <td><?php echo htmlspecialchars($row["completeDate"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No previous offers.</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="citizen_home.php" class="back-button">Back to Home</a>
</body>
</html>
