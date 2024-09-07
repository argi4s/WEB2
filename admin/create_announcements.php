<?php
require_once '../session_check.php';
check_login('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        /* CSS to adjust the width of the Actions column */
        .actions-col {
            width: 230px; /* Adjust the width as needed */
        }
    </style>
</head>
<body>
    <h1>Requests and Announcements</h1>

    <?php
    // Database connection
    $host = "localhost";
    $dbname = "vasi";
    $username = "root";
    $password = "";

    $conn = mysqli_connect($host, $username, $password, $dbname);

    if (mysqli_connect_errno()) {
        die("Connection error: " . mysqli_connect_error());
    }

    // Handle Create Announcement from Request
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["requestId"])) {
        $requestId = $_POST["requestId"];

        // Insert into announcements table
        $sql = "INSERT INTO announcements (announcements_requestId) VALUES (?)";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $requestId);
        mysqli_stmt_execute($stmt);

        echo "Announcement Created for Request ID: $requestId<br><br>";
    }

    // Handle Delete Announcement
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteAnnouncementId"])) {
        $announcementId = $_POST["deleteAnnouncementId"];

        // Delete from announcements table
        $sql = "DELETE FROM announcements WHERE announcementId = ?";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $announcementId);
        mysqli_stmt_execute($stmt);

        echo "Announcement Deleted ID: $announcementId<br><br>";
    }

    // Fetch and display requests with status 'pending'
    $result = mysqli_query($conn, "SELECT r.requestId, r.username, r.productId, r.quantity, r.createdAt, r.status, 
                                          p.productName, p.productCategory 
                                   FROM requests r 
                                   JOIN warehouse p ON r.productId = p.productId
                                   WHERE r.status = 'pending'");

    // Fetch existing announcements to disable the button
    $announcements = mysqli_query($conn, "SELECT announcements_requestId FROM announcements");
    $announcements_list = [];
    while ($row = $announcements->fetch_assoc()) {
        $announcements_list[] = $row['announcements_requestId'];
    }

    if ($result->num_rows > 0) {
        echo "<h2>Pending Requests</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Request ID</th>
                    <th>Username</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Created At</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $requestId = $row['requestId'];
            $isDisabled = in_array($requestId, $announcements_list) ? 'disabled' : '';
            echo "<tr>
                    <td>{$row['requestId']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['productName']}</td>
                    <td>{$row['productCategory']}</td>
                    <td>{$row['quantity']}</td>
                    <td>{$row['createdAt']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No pending requests found.";
    }

    // Display Create Announcement Button outside the table
    echo "<h2>Create Announcement</h2>";
    echo "<form method='POST' action=''>
            <label for='requestId'>Select Request ID:</label>
            <select name='requestId' id='requestId'>";
    
    // Populate the select dropdown with pending requests
    $result_pending = mysqli_query($conn, "SELECT r.requestId, r.status 
                                           FROM requests r 
                                           WHERE r.status = 'pending'");
    while ($row = $result_pending->fetch_assoc()) {
        $requestId = $row['requestId'];
        $isSelected = in_array($requestId, $announcements_list) ? 'disabled' : '';
        echo "<option value='{$requestId}' {$isSelected}>Request ID: {$requestId}</option>";
    }

    echo "  </select>
            <input type='submit' value='Create Announcement'>
          </form>";

    // Display Announcements with details from the request
    $result_announcements = mysqli_query($conn, "SELECT a.announcementId, r.productId, 
                                                        r.quantity, p.productName, p.productCategory 
                                                 FROM announcements a 
                                                 JOIN requests r ON a.announcements_requestId = r.requestId
                                                 JOIN warehouse p ON r.productId = p.productId");

    if ($result_announcements->num_rows > 0) {
        echo "<h2>Announcements</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Announcement ID</th>
                    <th>Product Name</th>
                    <th>Product Category</th>
                    <th>Product Quantity</th>
                    <th class='actions-col'>Actions</th> <!-- Adjusted column class -->
                </tr>";
        while ($row = $result_announcements->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['announcementId']}</td>
                    <td>{$row['productName']}</td>
                    <td>{$row['productCategory']}</td>
                    <td>{$row['quantity']}</td>
                    <td class='actions-col'>
                        <form method='POST' action=''>
                            <input type='hidden' name='deleteAnnouncementId' value='{$row['announcementId']}'>
                            <input type='submit' value='Delete Announcement'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No announcements found.";
    }

    mysqli_close($conn);
    ?>

    <br>
    <a href="admin_main_page.php" class="button">Main Page</a>
</body>
</html>
