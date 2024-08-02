<?php

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT r.*, c.name, c.surname, w.productName
FROM requests r
JOIN citizens c ON r.username = c.username
JOIN warehouse w ON r.productId = w.productId
WHERE r.status = 'pending';
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<div class="tasktainer request">
                    <div class="text">
                        <p class="bold-text">' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['productName']) . '</p>
                        <p class="subtext">' . htmlspecialchars($row['surname']) . ' ' . htmlspecialchars($row['name']) . '</p>
                        <p class="subtext">' . htmlspecialchars($row['createdAt']) . '</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <button class="button smallgreen" onclick="takeOnRequest(' . htmlspecialchars($row['requestId']) . ')">Take On</button>
                    </div>
              </div>';
    }
} else {
    echo '<div class="tasktainer">
            <div class="text" style="text-align: center; padding: 16px;">
                <p class="bold-text">No available requests</p>
            </div>
          </div>';
}

$conn->close();
?>

<script>
function takeOnRequest(requestId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "take_on_request.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = xhr.responseText;
            if (response.includes("Request taken successfully")) {
                alert('Request taken!');
                location.reload(); // Reload the page to reflect the changes
            } else if (response.includes("You already have 4 active tasks")) {
                alert('You already have 4 active tasks.');
            } else {
                alert('Error: ' + response);
            }
        }
    };
    xhr.send("requestId=" + requestId);
}
</script>