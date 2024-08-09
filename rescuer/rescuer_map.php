<?php
require_once '../session_check.php';
check_login('rescuer');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="./style.css">

    <title>Rescuer Map</title>
</head>

<body>
    <div class="container" style="display: flex; justify-content: start;">
        <div class="container" style="width: 300px; margin: 1%; display: flex; flex-direction: column;">
        
            <h1>My Tasks</h1>

            <div class="scroll-container" style="height: 50%;">
                <?php include 'fetch_my_tasks.php'; ?>
            </div>
            
            <div class="container" style="height: auto; width: 100%; display: grid; justify-content: end; margin-top: auto;">
                <button class="filter-btn active" id="filter1" onclick="applyFilter('filter1')">Requests</button>
                <button class="filter-btn active" id="filter2" onclick="applyFilter('filter2')">Offers</button>
                <button class="filter-btn active" id="filter3" onclick="applyFilter('filter3')">My Tasks</button>
                <button class="filter-btn active" id="filter4" onclick="applyFilter('filter4')">Rescuers</button>
            </div>

            <a href="rescuer_main_page.php" class="button back" style="margin-top: auto; margin-bottom: 25px;">Back to Main Page</a>
 
        </div>
        <div class="map-container">
            <div id="map"></div>
        </div>
        <div class="container" style="width: 300px; margin: 1%">

            <h1>Available Requests</h1>

            <div class="scroll-container">
                <?php include 'fetch_requests.php'; ?>
            </div>

            <h1>Available Offers</h1>

            <div class="scroll-container">
                <?php include 'fetch_offers.php'; ?>
            </div>

        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="rescuer_map.js"></script>
</body>
</html>
