<?php

session_start();

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

// Get the username from the session
$rescuerUsername = $_SESSION['username'];

// Fetch active tasks
$sql = "SELECT 
        rt.taskType as taskType,
        rt.requestId,
        rt.offerId,
        rt.taskId as id,
        c.name AS citizenName,
        c.surname AS citizenSurname,
        c.phone AS citizenPhone,
        c.latitude,
        c.longitude,
        w.productName as productName,
        COALESCE(r.quantity, o.quantity) AS quantity,
        COALESCE(r.createdAt, o.createdAt) AS createdAt,
        COALESCE(r.status, o.status) AS status
    FROM rescuer_tasks rt
    LEFT JOIN requests r ON rt.taskType = 'request' AND rt.requestId = r.requestId
    LEFT JOIN offers o ON rt.taskType = 'offer' AND rt.offerId = o.offerId
    LEFT JOIN citizens c ON (r.username = c.username OR o.username = c.username)
    LEFT JOIN warehouse w ON (r.productId = w.productId OR o.productId = w.productId)
    WHERE rt.rescuerUsername = ? AND (rt.taskType = 'request' AND r.status = 'taken' OR rt.taskType = 'offer' AND o.status = 'taken')
    ORDER BY createdAt";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rescuerUsername);
$stmt->execute();
$result = $stmt->get_result();

$features = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $features[] = [
            "type" => "Feature",
            "properties" => [
                "id" => $row["id"],
                "productName" => $row["productName"],
                "quantity" => $row["quantity"],
                "name" => $row["citizenName"],
                "surname" => $row["citizenSurname"],
                "phone" => $row["citizenPhone"],
                "createdAt" => $row["createdAt"],
                "status" => $row["status"],
                "taskType" => $row["taskType"]
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
