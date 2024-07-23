<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Warehouse Management</h1>
    <form id="productForm" method="POST" action="">
        <label for="productName">Product Name</label>
        <input type="text" id="productName" name="productName" required><br><br>
    
        <label for="productCategory">Product Category</label>
        <select id="productCategory" name="productCategory" required>
            <!-- Options will be populated by JavaScript -->
        </select><br><br>
        
        <label for="productQuantity">Product Quantity</label>
        <input type="number" id="productQuantity" name="productQuantity" required><br><br>
        
        <input type="submit" value="Add Product">
    </form>

    <script>
        // JavaScript function to populate the product categories
        document.addEventListener("DOMContentLoaded", function() {
            var categories = ["FOOD", "DRINK", "TOOL", "MEDS", "OTHER"];
            var select = document.getElementById("productCategory");

            categories.forEach(function(category) {
                var option = document.createElement("option");
                option.value = category;
                option.text = category;
                select.appendChild(option);
            });
        });
    </script>
    
    <?php
    $host = "localhost";
    $dbname = "vasi";
    $username = "root";
    $password = "";

    $conn = mysqli_connect($host, $username, $password, $dbname);

    if (mysqli_connect_errno()) {
        die("Connection error: " . mysqli_connect_error());
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["productName"])) {
        $productName = $_POST["productName"];
        $productCategory = $_POST["productCategory"];
        $productQuantity = filter_input(INPUT_POST, "productQuantity", FILTER_VALIDATE_INT);

        $sql = "INSERT INTO warehouse (productName, productCategory, productQuantity) VALUES (?, ?, ?)";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssi", $productName, $productCategory, $productQuantity);

        mysqli_stmt_execute($stmt);

        echo "Product Saved<br><br>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST["incrementId"]) || isset($_POST["decrementId"]))) {
        if (isset($_POST["incrementId"])) {
            $productId = $_POST["incrementId"];
            $sql = "UPDATE warehouse SET productQuantity = productQuantity + 1 WHERE productId = ?";
        } else if (isset($_POST["decrementId"])) {
            $productId = $_POST["decrementId"];
            $sql = "UPDATE warehouse SET productQuantity = IF(productQuantity > 0, productQuantity - 1, 0) WHERE productId = ?";
        }

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $productId);

        mysqli_stmt_execute($stmt);

        echo "Product Quantity Updated<br><br>";
    }

    // Fetch warehouse data
    $result = mysqli_query($conn, "SELECT * FROM warehouse ORDER BY productCategory");

    if ($result->num_rows > 0) {
        echo "<h2>Warehouse Products</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Product Category</th>
                    <th>Product Quantity</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['productId']}</td>
                    <td>{$row['productName']}</td>
                    <td>{$row['productCategory']}</td>
                    <td>{$row['productQuantity']}</td>
                    <td>
                        <form method='POST' action='' style='display:inline-block;'>
                            <input type='hidden' name='incrementId' value='{$row['productId']}'>
                            <input type='submit' value='+'>
                        </form>
                        <form method='POST' action='' style='display:inline-block;'>
                            <input type='hidden' name='decrementId' value='{$row['productId']}'>
                            <input type='submit' value='-'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No products found.";
    }

    // Fetch onvehicles data
    $vehicles_result = mysqli_query($conn, "SELECT * FROM onvehicles");

    if ($vehicles_result->num_rows > 0) {
        echo "<h2>Products on Vehicles</h2>";
        $vehicles = [];
        while ($row = $vehicles_result->fetch_assoc()) {
            $vehicles[$row['rescuerUsername']][] = $row;
        }

        foreach ($vehicles as $rescuerUsername => $products) {
            echo "<h3>Vehicle: $rescuerUsername</h3>";
            echo "<table border='1'>
                    <tr>
                        <th>Product Name</th>
                        <th>Product Quantity</th>
                    </tr>";
            foreach ($products as $product) {
                echo "<tr>
                        <td>{$product['productName']}</td>
                        <td>{$product['productQuantity']}</td>
                      </tr>";
            }
            echo "</table><br>";
        }
    } else {
        echo "No products on vehicles found.";
    }

    mysqli_close($conn);
    ?>
    <br>
    <a href="admin_main_page.html" class="button">Main Page</a>
</body>
</html>
