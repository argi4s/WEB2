<?php

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT o.*, c.name, c.surname, w.productName
FROM offers o
JOIN citizens c ON o.username = c.username
JOIN warehouse w ON o.productId = w.productId
WHERE o.status = 'pending';
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<div class="tasktainer offer">
                    <div class="text">
                        <p class="bold-text">' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['productName']) . '</p>
                        <p class="subtext">' . htmlspecialchars($row['surname']) . ' ' . htmlspecialchars($row['name']) . '</p>
                        <p class="subtext">' . htmlspecialchars($row['createdAt']) . '</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <button class="button smallgreen" onclick="takeOnOffer(' . htmlspecialchars($row['offerId']) . ')">Take On</button>
                    </div>
              </div>';
    }
} else {
    echo '<div class="tasktainer">
            <div class="text" style="text-align: center; padding: 16px;">
                <p class="bold-text">No available offers</p>
            </div>
          </div>';
}

$conn->close();
?>

<script>
function takeOnOffer(offerId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "take_on_offer.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert('Offer taken!');
            location.reload(); // Reload the page to reflect the changes
        }
    };
    xhr.send("offerId=" + offerId);
}
</script>