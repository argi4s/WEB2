<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Rescuer Account</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
        function validateForm() {
            const phone = document.getElementById('phone').value;
            const phoneRegex = /^\d{10}$/;

            if (!phoneRegex.test(phone)) {
                alert('Phone number must be a 10-digit number.');
                return false;
            }

            return true;
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            document.getElementById('latitude').value = 38.0167; // Default latitude for Chalandri, Athens
            document.getElementById('longitude').value = 23.8; // Default longitude for Chalandri, Athens
        });
    </script>
</head>
<body>
    <h1>Create Rescuer Account</h1>
    <form id="rescuerForm" method="POST" action="" onsubmit="return validateForm();">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required><br><br>
    
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="name">Name</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="surname">Surname</label>
        <input type="text" id="surname" name="surname" required><br><br>

        <label for="phone">Phone</label>
        <input type="number" id="phone" name="phone" required><br><br>

        <label for="latitude">Latitude</label>
        <input type="number" step="any" id="latitude" name="latitude" required><br><br>

        <label for="longitude">Longitude</label>
        <input type="number" step="any" id="longitude" name="longitude" required><br><br>

        <input type="submit" value="Create Account">
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

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $name = $_POST["name"];
        $surname = $_POST["surname"];
        $phone = $_POST["phone"];
        $latitude = $_POST["latitude"];
        $longitude = $_POST["longitude"];

        // Validate phone number server-side
        if (!preg_match('/^\d{10}$/', $phone)) {
            die("Phone number must be a 10-digit number.");
        }

        // Start a transaction
        mysqli_begin_transaction($conn);

        try {
            // Insert into users table
            $user_sql = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, 0)";
            $stmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($stmt, $user_sql)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "ss", $username, $password);
            mysqli_stmt_execute($stmt);

            // Insert into rescuers table
            $rescuer_sql = "INSERT INTO rescuers (username, name, surname, phone, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)";

            if (!mysqli_stmt_prepare($stmt, $rescuer_sql)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "ssssdd", $username, $name, $surname, $phone, $latitude, $longitude);
            mysqli_stmt_execute($stmt);

            // Commit transaction
            mysqli_commit($conn);

            echo "Rescuer account created successfully<br><br>";
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);

            echo "Failed to create rescuer account: " . $e->getMessage() . "<br><br>";
        }
    }

    // Fetch and display rescuers
    $result = mysqli_query($conn, "SELECT r.username, r.name, r.surname, r.phone, r.latitude, r.longitude FROM rescuers r JOIN users u ON r.username = u.username");

    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Surname</th>
                    <th>Phone</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['username']}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['surname']}</td>
                    <td>{$row['phone']}</td>
                    <td>{$row['latitude']}</td>
                    <td>{$row['longitude']}</td>
                    <td>
                        <form method='POST' action=''>
                            <input type='hidden' name='deleteUsername' value='{$row['username']}'>
                            <input type='submit' value='Delete'>
                        </form>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No rescuers found.";
    }

    // Handle deletion
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["deleteUsername"])) {
        $deleteUsername = $_POST["deleteUsername"];

        // Start a transaction
        mysqli_begin_transaction($conn);

        try {
            // Delete from rescuers table
            $rescuer_delete_sql = "DELETE FROM rescuers WHERE username = ?";
            $stmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($stmt, $rescuer_delete_sql)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "s", $deleteUsername);
            mysqli_stmt_execute($stmt);

            // Delete from users table
            $user_delete_sql = "DELETE FROM users WHERE username = ?";
            if (!mysqli_stmt_prepare($stmt, $user_delete_sql)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "s", $deleteUsername);
            mysqli_stmt_execute($stmt);

            // Commit transaction
            mysqli_commit($conn);

            echo "Rescuer deleted successfully<br><br>";
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);

            echo "Failed to delete rescuer: " . $e->getMessage() . "<br><br>";
        }
    }

    mysqli_close($conn);
    ?>
    <br>
    <a href="admin_main_page.html" class="button">Main Page</a>
</body>
</html>
