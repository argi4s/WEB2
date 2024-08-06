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

// Fetch pending requests
$sql = "SELECT o.*, c.name, c.surname, c.phone, c.latitude, c.longitude, w.productName
        FROM offers o
        JOIN citizens c ON o.username = c.username
        JOIN warehouse w ON o.productId = w.productId
        WHERE o.status = 'pending'
        ORDER BY createdAt";

$result = $conn->query($sql);

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
                "createdAt" => $row["createdAt"],
                "status" => $row["status"]
            ],
            "geometry" => [
                "type" => "Point",
                "coordinates" => [(float)$row["longitude"], (float)$row["latitude"]]
            ]
        ];
    }
}

$geojson = [
    "type" => "FeatureCollection",
    "features" => $features
];

echo json_encode($geojson);

$conn->close();
?>
