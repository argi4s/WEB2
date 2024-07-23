<?php
require_once '../session_check.php';
check_login('rescuer');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./style.css">
    <title>Rescuer Menu</title>
</head>

<body>
    <div class="container" style="height: auto;">
        
        <a href="rescuer_map.php" class="button yellow">Map/Tasks</a>
        
        <a href="vehicle_storage_page.php" class="button yellow">View Vehicle Storage</a>
        
        <a href="logout.php" class="button logout">Log out</a>

    </div>
</body>
</html>
