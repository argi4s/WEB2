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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">       <!--Classless CSS-->
</head>
<body>
    <h1>Admin Menu</h1>
    <form id="adminMenuForm">                                                                   <!--List of other pages-->
        <a href="warehouse.php">Show Warehouse</a><br><br>
        <a href="map.php">Show Map</a><br><br>
        <a href="statistics.php">Show Statistics</a><br><br>
        <a href="create_rescuer_account.php" class="yellow">Create Rescuer Account</a><br><br>
        <a href="create_announcements.php" class="yellow">Create Announcement</a><br><br>
        <a href="../logout.php" class="logout">Log out</a><br><br>
    </form>
</body>
</html>
