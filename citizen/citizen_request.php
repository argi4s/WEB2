<?php
require_once '../session_check.php';
check_login('citizen');

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

// Connect to the database
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (mysqli_connect_errno()) {
    die("Connection error: " . mysqli_connect_error());
}

$loggedInUser = isset($_SESSION['username']) ? $_SESSION['username'] : null;

if (!$loggedInUser) {
    die("You are not logged in. Please log in first.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $citizenUsername = $loggedInUser;
    $requestProductName = !empty($_POST['Product']) ? $_POST['Product'] : null;
    $requestProductQuantity = isset($_POST['Quantity']) ? (int)$_POST['Quantity'] : 0;
    $citizenProductCategory = !empty($_POST['ProductCategory']) ? $_POST['ProductCategory'] : null;
    $numberOfPeople = isset($_POST['NumberOfPeople']) ? (int)$_POST['NumberOfPeople'] : 0;

    // Check that at least one of Product Name or Product Category is provided
    if (!$requestProductName && !$citizenProductCategory) {
        die("You must provide either a product name or product category.");
    }

    // Retrieve the productId from the warehouse table based on the productName or productCategory
    $productId = null;
    if ($requestProductName || $citizenProductCategory) {
        // Check if the product exists
        $productCheckQuery = "SELECT productId FROM warehouse WHERE productName = ? OR productCategory = ?";
        $stmt = mysqli_prepare($conn, $productCheckQuery);
        mysqli_stmt_bind_param($stmt, "ss", $requestProductName, $citizenProductCategory);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if ($product) {
            $productId = $product['productId'];
        } else {
            // Insert the new product into the warehouse table with a default quantity of 0
            $insertProductQuery = "INSERT INTO warehouse (productName, productCategory, productQuantity) VALUES (?, ?, 0)";
            $stmt = mysqli_prepare($conn, $insertProductQuery);
            mysqli_stmt_bind_param($stmt, "ss", $requestProductName, $citizenProductCategory);
            mysqli_stmt_execute($stmt);

            // Retrieve the new productId
            $productId = mysqli_insert_id($conn);
        }
    }

    // Debugging: Print the productId to check if it's set correctly
    if ($productId === null) {
        die("Product ID is null. Check if the product was inserted or fetched correctly.");
    }

    // Insert data into requests table
    $sql = "INSERT INTO requests (username, productId, quantity, citizenProductCategory, requestProductName, numberOfPeople)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "siissi", $citizenUsername, $productId, $requestProductQuantity, $citizenProductCategory, $requestProductName, $numberOfPeople);

    // Execute the statement and check for errors
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing query: " . mysqli_stmt_error($stmt));
    }
}

// Fetch data for Pending Requests and Previous Requests
$pendingSql = "
    SELECT r.requestId, r.username, r.quantity, r.numberOfPeople, r.status, w.productName, w.productCategory
    FROM requests r
    JOIN warehouse w ON r.productId = w.productId
    WHERE r.status = 'pending'
";

$previousSql = "
    SELECT r.requestId, r.username, r.quantity, r.numberOfPeople, r.status, r.acceptDate, r.completeDate, w.productName, w.productCategory
    FROM requests r
    JOIN warehouse w ON r.productId = w.productId
    WHERE r.status != 'pending'
";

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
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
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
    <script>
        function fetchAndSetProductName() {
            const category = document.getElementById('productCategory').value.trim();
            const productField = document.getElementById('product');

            // Only proceed if a category is selected
            if (category) {
                // Create an AJAX request
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'fetch_product.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.productName) {
                                productField.value = response.productName;
                            } else {
                                productField.value = '';
                            }
                        } else {
                            console.error('Failed to fetch product name.');
                        }
                    }
                };

                // Send the request with the selected category
                xhr.send('category=' + encodeURIComponent(category));
            }
        }

        function validateForm() {
            const product = document.getElementById('product').value.trim();
            const category = document.getElementById('productCategory').value.trim();

            // Ensure at least one field is filled
            if (!product && !category) {
                alert("You must provide either a product name or product category.");
                return false;
            }

            // Ensure the product name matches one of the provided options
            const validProducts = ["Water", "Bread", "Hammer", "Bandages", "Milk"];
            if (product && !validProducts.includes(product)) {
                alert("Please select a valid product name from the list.");
                return false;
            }

            // Ensure the product category matches one of the provided options
            const validCategories = ["FOOD", "DRINK", "MEDS", "TOOL", "OTHER"];
            if (category && !validCategories.includes(category)) {
                alert("Please select a valid product category from the list.");
                return false;
            }

            return true; // Proceed with form submission if validation passes
        }
    </script>
</head>

<body>
    <h1>Request</h1>

    <!-- Display logged-in user's username -->
    <p>Logged in as: <strong><?php echo htmlspecialchars($loggedInUser); ?></strong></p>

    <!-- Back button -->
    <a href="citizen_main_page.php" class="back-button">Go Back</a>

    <form id="request_form" method="POST" action="" onsubmit="return validateForm();">
        <div style="display: flex; align-items: center; gap: 20px;">
            <label for="product">Product:</label>
            <input list="product-options" id="product" name="Product" placeholder="Type or select a product...">
            <datalist id="product-options">
                <option value="Water">
                <option value="Bread">
                <option value="Hammer">
                <option value="Bandages">
                <option value="Milk">
            </datalist>

            <label for="requestProductQuantity">Product Quantity:</label>
            <input type="number" id="requestProductQuantity" name="Quantity" placeholder="Type the amount of products you want" required>

            <label for="productCategory">Product Category:</label>
            <input list="category-options" id="productCategory" name="ProductCategory" placeholder="Select a category..." onchange="fetchAndSetProductName()">
            <datalist id="category-options">
                <option value="FOOD">
                <option value="DRINK">
                <option value="MEDS">
                <option value="TOOL">
                <option value="OTHER">
            </datalist>

            <label for="numberOfPeople">Number of People:</label>
            <input type="number" id="numberOfPeople" name="NumberOfPeople" placeholder="Enter the number of people" required>
        </div>

        <h3><input type="submit" value="Request"></h3>
    </form>

    <h2>Pending Requests</h2>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Quantity</th>
                <th>Product Category</th>
                <th>Number of People</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($pendingResult)): ?>
               <tr>
                    <td><?php echo htmlspecialchars($row['productName'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity'] ?? '0'); ?></td>
                    <td><?php echo htmlspecialchars($row['productCategory'] ?? 'Not Set'); ?></td>
                    <td><?php echo htmlspecialchars($row['numberOfPeople'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['status'] ?? 'pending'); ?></td>
               </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Previous Requests</h2>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Product Quantity</th>
                <th>Product Category</th>
                <th>Number of People</th>
                <th>Status</th>
                <th>Accept Date</th>
                <th>Complete Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($previousResult)): ?>
               <tr>
                    <td><?php echo htmlspecialchars($row['productName'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity'] ?? '0'); ?></td>
                    <td><?php echo htmlspecialchars($row['productCategory'] ?? 'Not Set'); ?></td>
                    <td><?php echo htmlspecialchars($row['numberOfPeople'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['status'] ?? 'finished'); ?></td>
                    <td><?php echo htmlspecialchars($row['acceptDate'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($row['completeDate'] ?? ''); ?></td>
               </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
