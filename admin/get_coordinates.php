<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch rescuers with usernames, latitude, and longitude
$rescuers_sql = "SELECT username, latitude, longitude FROM rescuers";
$rescuers_result = $conn->query($rescuers_sql);

$rescuers = [];
if ($rescuers_result->num_rows > 0) {
    while ($row = $rescuers_result->fetch_assoc()) {
        // Fetch products associated with this rescuer
        $products_sql = "SELECT productName, productQuantity FROM onvehicles WHERE rescuerUsername = ?";
        $stmt = $conn->prepare($products_sql);
        $stmt->bind_param("s", $row['username']);
        $stmt->execute();
        $products_result = $stmt->get_result();
        
        $products = [];
        while ($product_row = $products_result->fetch_assoc()) {
            $products[] = $product_row;
        }

        // Add rescuer info and products to the array
        $rescuers[] = [
            'username' => $row['username'],
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'products' => $products
        ];
    }
}

// Fetch citizens
$citizens_sql = "SELECT name, surname, phone, latitude, longitude FROM citizens";
$citizens_result = $conn->query($citizens_sql);

$citizens = [];
if ($citizens_result->num_rows > 0) {
    while ($row = $citizens_result->fetch_assoc()) {
        $citizens[] = $row;
    }
}

echo json_encode(['rescuers' => $rescuers, 'citizens' => $citizens]);

$conn->close();
?>
