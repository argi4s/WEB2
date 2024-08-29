<?php
session_start();

$host = "localhost";
$dbname = "vasi";
$username = "root";
$password = "";

// Connect to the database
$conn = mysqli_connect($host, $username, $password, $dbname);

if (mysqli_connect_errno()) {
    die("Connection error: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $citizenUsername = 'citizen1'; // Replace with session username or dynamically
    $requestProductName = $_POST['Product'];
    $requestProductQuantity = isset($_POST['Quantity']) ? (int)$_POST['Quantity'] : 0;
    $requestPeopleQuantity = (int)$_POST['PeopleQuantity'];
    $citizenProductCategory = isset($_POST['ProductCategory']) ? $_POST['ProductCategory'] : null;

    // Insert data into citizen_requests table
    $sql = "INSERT INTO citizen_requests (citizenUsername, requestProductName, requestProductQuantity, requestPeopleQuantity, citizenProductCategory)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssiii", $citizenUsername, $requestProductName, $requestProductQuantity, $requestPeopleQuantity, $citizenProductCategory);
    mysqli_stmt_execute($stmt);
}

// Fetch data for Pending Requests and Previous Requests
$pendingSql = "SELECT * FROM citizen_requests WHERE acceptDate IS NULL AND completeDate IS NULL";
$previousSql = "SELECT * FROM citizen_requests WHERE acceptDate IS NOT NULL OR completeDate IS NOT NULL";

$pendingResult = mysqli_query($conn, $pendingSql);
$previousResult = mysqli_query($conn, $previousSql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
    table {
        width: 100%; /* Ensure the table takes the full width of its container */
        border-collapse: collapse; /* Collapse borders into a single border for a cleaner look */
        margin: 20px 0; /* Add some margin above and below the table */
    }
    table, th, td {
        border: 1px solid black; /* Add a solid black border to the table and its cells */
    }
    th, td {
        padding: 8px; /* Add padding inside table headers and cells for spacing */
        text-align: left; /* Align text to the left inside table headers and cells */
    }
</style>
</head>

<body>
    <h1>Request</h1>
    <a href="citizen_main_page.php" class="button" style="position:absolute;bottom:0%;">Go Back</a>

    <form id="request_form" method="POST" action="">
        <div style="display: flex; align-items: center; gap: 20px;">
            <label for="product">Product:</label>
            <input list="product-options" id="product" name="Product" placeholder="Type or select a product..." required>
            <datalist id="product-options">
                <option value="FOOD">
                <option value="DRINK">
                <option value="TOOL">
                <option value="OTHER">
                <option value="PROTEIN BARS">
                <option value="WATER">
                <option value="BANDAGES">
            </datalist>

            <label for="requestProductQuantity">Product Quantity:</label>
            <input type="number" id="requestProductQuantity" name="Quantity" placeholder="Type the amount of products you want" >

            <label for="requestPeopleQuantity">People Quantity:</label>
            <input type="number" id="requestPeopleQuantity" name="PeopleQuantity" placeholder="Type the amount of people there are" required>
        </div>

        <h3><input type="submit" value="Request"></h3>
    </form>

    <h2>Pending Requests</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Product Name</th>
                <th>Product Quantity</th>
                <th>People Quantity</th>
                <th>Product Category</th>
                <th>Accept Date</th>
                <th>Complete Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($pendingResult)): ?>
               <tr>
                    <td><?php echo htmlspecialchars($row['citizenUsername'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['requestProductName'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['requestProductQuantity'] ?? '0'); ?></td>
                    <td><?php echo htmlspecialchars($row['requestPeopleQuantity'] ?? '0'); ?></td>
                    <td><?php echo htmlspecialchars($row['citizenProductCategory'] ?? 'Not Set'); ?></td>
                    <td><?php echo ($row['acceptDate'] === NULL) ? 'Not Set' : htmlspecialchars($row['acceptDate']); ?></td>
                    <td><?php echo ($row['completeDate'] === NULL) ? 'Not Set' : htmlspecialchars($row['completeDate']); ?></td>
               </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Previous Requests</h2>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Product Name</th>
                <th>Product Quantity</th>
                <th>People Quantity</th>
                <th>Product Category</th>
                <th>Accept Date</th>
                <th>Complete Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($previousResult)): ?>
               <tr>
                    <td><?php echo htmlspecialchars($row['citizenUsername'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['requestProductName'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['requestProductQuantity'] ?? '0'); ?></td>
                    <td><?php echo htmlspecialchars($row['requestPeopleQuantity'] ?? '0'); ?></td>
                    <td><?php echo htmlspecialchars($row['citizenProductCategory'] ?? 'Not Set'); ?></td>
                    <td><?php echo ($row['acceptDate'] === NULL) ? 'Not Set' : htmlspecialchars($row['acceptDate']); ?></td>
                    <td><?php echo ($row['completeDate'] === NULL) ? 'Not Set' : htmlspecialchars($row['completeDate']); ?></td>
               </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
