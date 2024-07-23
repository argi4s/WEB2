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

// Fetch rescuers with usernames
$rescuers_sql = "SELECT username, latitude, longitude FROM rescuers";
$rescuers_result = $conn->query($rescuers_sql);

$rescuers = [];
if ($rescuers_result->num_rows > 0) {
    while ($row = $rescuers_result->fetch_assoc()) {
        $rescuers[] = $row;
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

/*    ------------PAPADEROS-----------
Diaxeirisths 3) Probolh xarth b) (kai paromoia fash to c, trekse ton xarth kai pata ta popups gia na katalabeis ti kanoune)
Na breis me poio querry mporeis na emfaniseis ta zhtoumena. Gia to php/js kommati mhn anhsuxeis, ta apo panw paradeigmata mazi me to map_functionality.js arxeio tha se kathodhghsoun
// Fetch requests
$requests_sql = "SELECT createdAt, productId, quantity FROM requests";
$requests_result = $conn->query($requests_sql);

$requests = [];
if ($requests_result->num_rows > 0) {
    while ($row = $requests_result->fetch_assoc()) {
        $requests[] = $row;
    }
}
*/

echo json_encode(['rescuers' => $rescuers, 'citizens' => $citizens]);   //isws xreiastei na baleis ta requests kai edw

$conn->close();
?>
