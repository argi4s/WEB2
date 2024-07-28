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
    <title>Vehicle Storage</title>
</head>

<body>
    <div class="container" style="height: auto; width: auto;">
        <div class="itemlist">
            <h2>Vehicle's Storage</h2>
            <?php include 'fetch_vehicle_storage.php'; ?>
        </div>
        
        <a href="add_item_page.php" class="button green">Add Item</a>
        
        <a href="rescuer_main_page.php" class="button back">Back to Main Page</a>

    </div>
</body>
</html>
