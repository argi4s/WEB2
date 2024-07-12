<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Create Announcements </h1>
    <form id="announcementForm" method="POST" action="">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required><br><br>
    
        <label for="description">Description</label>
        <input type="text" id="description" name="description" required><br><br>
        
        <input type="submit" value="Create Announcement">
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

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["title"])) {
        $title = $_POST["title"];
        $description = $_POST["description"];
        
        $sql = "INSERT INTO announcements (title, description) VALUES (?, ?)";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ss", $title, $description);

        mysqli_stmt_execute($stmt);

        echo "Announcement Saved<br><br>";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteId"])) {
        $deleteId = $_POST["deleteId"];

        $sql = "DELETE FROM announcements WHERE announcementId = ?";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $deleteId);

        mysqli_stmt_execute($stmt);

        echo "Announcement Deleted<br><br>";
    }

    $result = mysqli_query($conn, "SELECT * FROM announcements");

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Announcement ID</th>
                    <th>Announcement Title</th>
                    <th>Announcement Description</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['announcementId']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['description']}</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='deleteId' value='{$row['announcementId']}'>
                            <input type='submit' value='Delete'>
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
    <a href="admin_main_page.html" class="button">Main Page</a>
</body>
</html>
