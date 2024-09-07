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

// Prepare the query to fetch inactive rescuers (those who have not taken any request or offer) and what they carry
$sql = "SELECT r.username, r.name, r.surname, r.phone, r.latitude, r.longitude,
               GROUP_CONCAT(ov.productName, ' (', ov.productQuantity, ')') AS vehicleProducts
        FROM rescuers r
        LEFT JOIN rescuer_tasks rt ON r.username = rt.rescuerUsername
        LEFT JOIN onvehicles ov ON r.username = ov.rescuerUsername
        WHERE rt.rescuerUsername IS NULL
        GROUP BY r.username";

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
                "username" => $row["username"],
                "name" => $row["name"],
                "surname" => $row["surname"],
                "phone" => $row["phone"],
                "vehicleProducts" => $row["vehicleProducts"] ? $row["vehicleProducts"] : "No products"
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
