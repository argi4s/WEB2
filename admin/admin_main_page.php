<?php
require_once '../session_check.php';
check_login('admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">  <!-- Classless CSS -->
    <style>
        .nav-button {
            display: block;
            width: 200px;
            margin: 10px auto;
            padding: 15px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            background-color: #f0f0f0;
            border: 2px solid #ccc;
            border-radius: 8px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .nav-button:hover {
            background-color: #e0e0e0;
            border-color: #888;
        }

        .yellow {
            background-color: #ffdd57;
            border-color: #ffcc00;
        }

        .yellow:hover {
            background-color: #ffd633;
        }

        .logout {
            background-color: #ff7777;
            border-color: #ff5555;
        }

        .logout:hover {
            background-color: #ff4444;
        }
    </style>
</head>
<body>
    <h1>Admin Menu</h1>

    <nav>
        <a href="warehouse.php" class="nav-button">Show Warehouse</a>
        <a href="map.php" class="nav-button">Show Map</a>
        <a href="statistics.php" class="nav-button">Show Statistics</a>
        <a href="create_rescuer_account.php" class="nav-button yellow">Create Rescuer Account</a>
        <a href="create_announcements.php" class="nav-button yellow">Create Announcement</a>
        <a href="../logout.php" class="nav-button logout">Log out</a>
    </nav>

</body>
</html>
