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

// Prepare the query to fetch pending offers with rescuer info
$sql = "SELECT o.*, c.name, c.surname, c.phone, c.latitude, c.longitude, w.productName, rt.rescuerUsername
        FROM offers o
        JOIN citizens c ON o.username = c.username
        JOIN warehouse w ON o.productId = w.productId
        LEFT JOIN rescuer_tasks rt ON o.offerId = rt.offerId AND rt.taskType = 'offer'
        WHERE o.status = 'pending'
        ORDER BY o.createdAt";

$result = $conn->query($sql);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Initialize the GeoJSON structure
$features = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $features[] = [
            "type" => "Feature",
            "properties" => [
                "offerId" => $row["offerId"],
                "productName" => $row["productName"],
                "quantity" => $row["quantity"],
                "name" => $row["name"],
                "surname" => $row["surname"],
                "phone" => $row["phone"],
                "createdAt" => date("Y-m-d H:i:s", strtotime($row["createdAt"])), // Ensures proper formatting
                "status" => $row["status"],
                "rescuerUsername" => $row["rescuerUsername"] ? $row["rescuerUsername"] : "Not assigned"  // Check if a rescuer is assigned
            ],
            "geometry" => [
                "type" => "Point",
                "coordinates" => [(float)$row["longitude"], (float)$row["latitude"]]
            ]
        ];
    }
}

// Prepare GeoJSON response
$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

// Set JSON header and output the GeoJSON
header('Content-Type: application/json');
echo json_encode($geojson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
