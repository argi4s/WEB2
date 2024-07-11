<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Rescuer Account</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Create Rescuer Account </h1>
    <form id="rescuerForm" method="POST" action="">
        <label for="rescuerName">Rescuer Name</label>
        <input type="text" id="rescuerName" name="rescuerName" required><br><br>
    
        <label for="rescuerSurname">Rescuer Surname</label>
        <input type="text" id="rescuerSurname" name="rescuerSurname" required><br><br>
        
        <label for="rescuerUsername">Rescuer Username</label>
        <input type="text" id="rescuerUsername" name="rescuerUsername" required><br><br>

        <label for="rescuerPassword">Rescuer Password</label>
        <input type="text" id="rescuerPassword" name="rescuerPassword" required><br><br>
        
        <input type="submit" value="Create Account">
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

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["rescuerName"])) {
        $rescuerName = $_POST["rescuerName"];
        $rescuerSurname = $_POST["rescuerSurname"];
        $rescuerUsername = $_POST["rescuerUsername"];
        $rescuerPassword = $_POST["rescuerPassword"];
        
        $sql = "INSERT INTO rescuer_account (rescuerName, rescuerSurname, rescuerUsername, rescuerPassword) VALUES (?, ?, ?, ?)";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssss", $rescuerName, $rescuerSurname, $rescuerUsername, $rescuerPassword);

        mysqli_stmt_execute($stmt);

        echo "Rescuer Saved<br><br>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteId"])) {
        $deleteId = $_POST["deleteId"];

        $sql = "DELETE FROM rescuer_account WHERE rescuerId = ?";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $deleteId);

        mysqli_stmt_execute($stmt);

        echo "Rescuer Deleted<br><br>";
    }

    $result = mysqli_query($conn, "SELECT * FROM rescuer_account");

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Rescuer ID</th>
                    <th>Rescuer Name</th>
                    <th>Rescuer Surname</th>
                    <th>Rescuer Username</th>
                    <th>Rescuer Password</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['rescuerId']}</td>
                    <td>{$row['rescuerName']}</td>
                    <td>{$row['rescuerSurname']}</td>
                    <td>{$row['rescuerUsername']}</td>
                    <td>{$row['rescuerPassword']}</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='deleteId' value='{$row['rescuerId']}'>
                            <input type='submit' value='Delete'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No rescuers found.";
    }
    mysqli_close($conn);
    ?>
    <br>
    <a href="admin_main_page.html" class="button">Main Page</a>
</body>
</html>
