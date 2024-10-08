<?php
require_once '../session_check.php';
check_login('citizen');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <style>
        body {
            background-color: hsl(0, 0%, 99%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            text-align: center;
        }
        .button {
            display: block;
            width: 200px;
            padding: 15px;
            margin: 10px auto;
            border: none;
            background-color: #f44336;
            color: white;
            font-size: 18px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button.yellow {
            background-color: #d2c966;
        }
        .button.logout{
            background-color: orange;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="citizen_request.php" class="button">Request Goods</a>
        
        <a href="citizen_announcement_offers.php" class="button yellow">Offers Announcement</a>

        <a href="../logout.php" class="button logout">Log out</a>
    </div>
</body>
</html>