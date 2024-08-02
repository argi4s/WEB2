<?php
// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "vasi";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$username = $_POST['username'];
$password = $_POST['password'];
$name = $_POST['name'];
$surname = $_POST['surname'];
$phone = $_POST['phone'];
$latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
$longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;

// Validate latitude and longitude
if (!is_numeric($latitude) || !is_numeric($longitude)) {
    header("Location: register.php?error=location_required");
    exit();
}

// Check if username already exists
$sql_check = "SELECT username FROM users WHERE username = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: register.php?error=username_exists");
    exit();
} else {
    // Insert into users table
    $sql_insert_user = "INSERT INTO users (username, password, is_admin) VALUES (?, ?, false)";
    $stmt_user = $conn->prepare($sql_insert_user);
    $stmt_user->bind_param("ss", $username, $password);
    $stmt_user->execute();
    
    // Insert into citizens table
    $sql_insert_citizen = "INSERT INTO citizens (username, name, surname, phone, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_citizen = $conn->prepare($sql_insert_citizen);
    $stmt_citizen->bind_param("ssssdd", $username, $name, $surname, $phone, $latitude, $longitude);
    $stmt_citizen->execute();

    header("Location: register.php?success=registered");
    exit();
}

// Close connections
$stmt->close();
$conn->close();
?>