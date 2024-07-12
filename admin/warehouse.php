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
    <form id="itemForm" method="POST" action="">
        <label for="itemName">Item Name</label>
        <input type="text" id="itemName" name="itemName" required><br><br>
    
        <label for="itemCategory">Item Category</label>
        <input type="text" id="itemCategory" name="itemCategory" required><br><br>
        
        <label for="itemQuantity">Item Quantity</label>
        <input type="number" id="itemQuantity" name="itemQuantity" required><br><br>
        
        <input type="submit" value="Add item">
    </form>
    
    <?php
    $host = "localhost";
    $dbname = "WEB2";
    $username = "root";
    $password = "";

    $conn = mysqli_connect($host, $username, $password, $dbname);

    if (mysqli_connect_errno()) {
        die("Connection error: " . mysqli_connect_error());
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["itemName"])) {
        $itemName = $_POST["itemName"];
        $itemCategory = $_POST["itemCategory"];
        $itemQuantity = filter_input(INPUT_POST, "itemQuantity", FILTER_VALIDATE_INT);

        $sql = "INSERT INTO warehouse (itemName, itemCategory, itemQuantity) VALUES (?, ?, ?)";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssi", $itemName, $itemCategory, $itemQuantity);

        mysqli_stmt_execute($stmt);

        echo "Item Saved<br><br>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteId"])) {
        $deleteId = $_POST["deleteId"];

        $sql = "DELETE FROM warehouse WHERE itemId = ?";

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
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Item Category</th>
                    <th>Item Quantity</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['itemId']}</td>
                    <td>{$row['itemName']}</td>
                    <td>{$row['itemCategory']}</td>
                    <td>{$row['itemQuantity']}</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='deleteId' value='{$row['itemId']}'>
                            <input type='submit' value='Delete'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No items found.";
    }
    mysqli_close($conn);
    ?>
    <br>
    <a href="admin_main_page.html" class="button">Main Page</a>
</body>
</html>
