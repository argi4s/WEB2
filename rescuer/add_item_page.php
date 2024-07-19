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
            <form action="add_item_page.html" method="post">
                <div class="form-group">
                    <label for="name">Item Name:</label>
                        <select id="name" name="name" required>
                            <?php include 'fetch_products.php'; ?>
                        </select> 
                </div>
                <div class="form-group">
                    <label for="quantity">Item Quantity:</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
            </form>
        </div>
        <div class="container" style="display: flex; justify-content: center; gap: 10px;">
        
            <a href="vehicle_storage_page.html" class="button">Cancel</a>
        
            <a href="vehicle_storage_page.html" class="button green">Add</a>

        </div>
    </div>
</body>
</html>
