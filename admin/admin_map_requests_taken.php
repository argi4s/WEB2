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
$sql = "SELECT r.*, c.name, c.surname, c.phone, c.latitude, c.longitude, w.productName, rt.rescuerUsername
        FROM requests r
        JOIN citizens c ON r.username = c.username
        JOIN warehouse w ON r.productId = w.productId
        LEFT JOIN rescuer_tasks rt ON r.requestId = rt.requestId 
        WHERE r.status = 'taken'    --For taken requests
        ORDER BY r.createdAt";

$result = $conn->query($sql);   

$features = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $features[] = [
            "type" => "Feature",
            "properties" => [
                "requestId" => $row["requestId"],
                "productName" => $row["productName"],
                "quantity" => $row["quantity"],
                "name" => $row["name"],
                "surname" => $row["surname"],
                "phone" => $row["phone"],
                "status" => $row["status"],
                "rescuerUsername" => $row["rescuerUsername"],
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
