<?php
require_once '../session_check.php';
check_login('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="../rescuer/style.css">

    <title>Admin Map</title>
</head>

<body>
    <div class="container" style="display: flex; justify-content: start;">
        <div class="container" style="width: 300px; margin: 1%; display: flex; flex-direction: column;">
            
            <div class="container" style="height: auto; width: 100%; display: grid; justify-content: end; margin-top: auto;">
                <button class="filter-btn active" id="filter1" onclick="applyFilter('filter1')">Pending Requests</button>
                <button class="filter-btn active" id="filter2" onclick="applyFilter('filter2')">Taken Requests</button>
                <button class="filter-btn active" id="filter3" onclick="applyFilter('filter3')">Pending Offers</button>
                <button class="filter-btn active" id="filter4" onclick="applyFilter('filter4')">Taken Offers</button>
                <button class="filter-btn active" id="filter5" onclick="applyFilter('filter5')">Active Rescuers</button>
                <button class="filter-btn active" id="filter6" onclick="applyFilter('filter6')">Inactive Rescuers</button>
            </div>

            <a href="admin_main_page.php" class="button back" style="margin-top: auto; margin-bottom: 25px;">Back to Main Page</a>
 
        </div>
        <div class="map-container">
            <div id="map"></div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="map_functionality.js"></script>
</body>
</html>
