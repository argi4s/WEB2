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
        <label for="productName">product Name</label>
        <input type="text" id="productName" name="productName" required><br><br>
    
        <label for="productCategory">product Category</label>
        <input type="text" id="productCategory" name="productCategory" required><br><br>
        
        <label for="productQuantity">product Quantity</label>
        <input type="number" id="productQuantity" name="productQuantity" required><br><br>
        
        <input type="submit" value="Add product">
    </form>
    
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

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteId"])) {
        $deleteId = $_POST["deleteId"];

        $sql = "DELETE FROM warehouse WHERE productId = ?";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $deleteId);

        mysqli_stmt_execute($stmt);

        echo "Record Deleted<br><br>";
    }

    $result = mysqli_query($conn, "SELECT * FROM warehouse");

    if ($result->num_rows > 0) {
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
                        <form method='POST' action=''>
                            <input type='hidden' name='deleteId' value='{$row['productId']}'>
                            <input type='submit' value='Delete'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No products found.";
    }
    mysqli_close($conn);
    ?>
    <br>
    <a href="admin_main_page.html" class="button">Main Page</a>
</body>
</html>
