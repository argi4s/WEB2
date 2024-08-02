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

        <button id="unloadAllButton" class="button red">Unload all</button>
        
        <a href="rescuer_main_page.php" class="button back">Back to Main Page</a>

    </div>

    <script>
        document.getElementById('unloadAllButton').addEventListener('click', async function() {
            try {
                const response = await fetch('unload_items.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const result = await response.json();

                if (result.success) {
                    alert('Items moved to warehouse successfully.');
                    location.reload(); // Refresh the page
                } else {
                    alert('Operation failed: ' + result.message);
                }
            } catch (error) {
                console.error('There was a problem with the fetch operation:', error);
                alert('An error occurred. Please try again.');
            }
        });
    </script>

</body>
</html>
