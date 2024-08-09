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
    <title>Add Items</title>
</head>

<body>
    <div class="container" style="height: auto; width: auto;">
        <div class="form-container">
            <h2>What item are you adding?</h2>
            <form id="addItemForm">
                <div class="form-group">
                    <label for="name">Item Name:</label>
                        <select id="name" name="name" required>
                            <?php include 'fetch_products.php'; ?>
                        </select> 
                </div>
                <div class="form-group">
                    <label for="quantity">Item Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" required>
                </div>
                <div class="container" style="display: flex; justify-content: center; gap: 10px;">
                    <a href="vehicle_storage_page.php" class="button">Go Back</a>
                    <button type="submit" class="button green">Add</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addItemForm').addEventListener('submit', async function(event) {
            event.preventDefault();
            const name = document.getElementById('name').value;
            const quantity = document.getElementById('quantity').value;

            console.log('Form element:', document.getElementById('addItemForm'));

            try {
            const response = await fetch('add_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, quantity })
            });

            if (!response.ok) {
                console.error('Fetch error:', response.status, response.statusText);
                throw new Error('Network response was not ok');
            }

            const result = await response.json();

            if (result.success) {
                alert('Item added successfully.');
                console.log('Resetting form...');
                document.getElementById('addItemForm').reset();  // Reset the form
            } else {
                alert('Item addition failed: ' + result.message);
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
            alert('An error occurred. Please try again.');
        }
        });

    </script>
</body>
</html>
