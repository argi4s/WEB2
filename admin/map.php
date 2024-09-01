<?php
require_once '../session_check.php';
check_login('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map with Filters</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .sidebar {
            width: 200px;
            background-color: #d2a07e;
            padding: 10px;
            box-sizing: border-box;
        }
        .button {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            background-color: #f44336;
            color: white;
            font-size: 16px;
            text-align: left;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button.green {
            background-color: #66d266;
        }
        .button.exit {
            background-color: aqua;
        }
        .map-container {
            flex-grow: 1;
        }
        #map {
            width: 100%;
            height: 100vh;
        }
        input[type=checkbox] {
            margin-right: 10px;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>
    <div class="sidebar"> <!-- Create sidebar for filters-->
        <div>
            <input type="checkbox" id="filters" class="filter-checkbox" checked hidden> <!-- Hide the checkbox -->
            <label for="filters" class="button green">Filters</label> <!-- Green rectangle for filters -->
        </div>    
        <div>
            <input type="checkbox" id="rescuersOnDuty" class="filter-checkbox" checked>
            <label for="rescuersOnDuty" class="button">Rescuers on duty</label>
        </div>
        <div>
            <input type="checkbox" id="rescuersOffDuty" class="filter-checkbox" checked>
            <label for="rescuersOffDuty" class="button">Rescuers off duty</label>
        </div>
        <div>
            <input type="checkbox" id="pendingRequests" class="filter-checkbox" checked>
            <label for="pendingRequests" class="button">Pending Requests</label>
        </div>
        <div>
            <input type="checkbox" id="completedRequests" class="filter-checkbox" checked>
            <label for="completedRequests" class="button">Completed Requests</label>
        </div>
        <div>
            <input type="checkbox" id="offers" class="filter-checkbox" checked>
            <label for="offers" class="button">Offers</label>
        </div>
        <div>
            <input type="checkbox" id="exit" class="filter-checkbox" checked hidden> <!-- Hide the checkbox -->
            <label for="exit-button" class="button exit" onclick="location.href='admin_main_page.php'">Exit</label> <!-- Aqua button for exiting -->
        </div>        
    </div>
    <div class="map-container">
        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="map_functionality.js"></script>
</body>
</html>
