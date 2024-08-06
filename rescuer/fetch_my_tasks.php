<?php

$servername = "localhost";
$username = "root"; 
$password = "";    
$dbname = "vasi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from the session
$rescuerUsername = $_SESSION['username'];

$sql = "
    SELECT 
        rt.taskType as taskType,
        rt.requestId,
        rt.offerId,
        rt.taskId as id,
        c.name AS citizenName,
        c.surname AS citizenSurname,
        c.phone AS citizenPhone,
        w.productName as productName,
        COALESCE(r.quantity, o.quantity) AS quantity,
        COALESCE(r.createdAt, o.createdAt) AS createdAt,
        COALESCE(r.status, o.status) AS status
    FROM rescuer_tasks rt
    LEFT JOIN requests r ON rt.taskType = 'request' AND rt.requestId = r.requestId
    LEFT JOIN offers o ON rt.taskType = 'offer' AND rt.offerId = o.offerId
    LEFT JOIN citizens c ON (r.username = c.username OR o.username = c.username)
    LEFT JOIN warehouse w ON (r.productId = w.productId OR o.productId = w.productId)
    WHERE rt.rescuerUsername = ? AND (rt.taskType = 'request' AND r.status = 'taken' OR rt.taskType = 'offer' AND o.status = 'taken')
    ORDER BY createdAt";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rescuerUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Output the data for each row
    while ($row = $result->fetch_assoc()) {
        echo '<div class="tasktainer ' . htmlspecialchars($row['taskType']) . '">
                    <div class="text">
                        <p class="bold-text">' . htmlspecialchars($row['quantity']) . ' ' . htmlspecialchars($row['productName']) . '</p>
                        <p class="subtext">' . htmlspecialchars($row['citizenName']) . ' ' . htmlspecialchars($row['citizenSurname']) . ' - ' . htmlspecialchars($row['citizenPhone']) . '</p>
                        <p class="subtext">' . htmlspecialchars($row['createdAt']) . '</p>
                    </div>
                    <div class="container" style="display: flex; justify-content: center;">
                        <a class="button smallred" onclick="cancelTask(' . htmlspecialchars($row['id']) . ')">Cancel</a>
                        <a class="button smallgreen" onclick="finishTask(' . htmlspecialchars($row['id']) . ')">Finish</a>
                    </div>
                </div>';
    }
} else {
    echo '<div class="tasktainer">
            <div class="text" style="text-align: center; padding: 16px;">
                <p class="bold-text">No tasks taken</p>
            </div>
        </div>';
}

// Close the statement and connection
$stmt->close();
$conn->close();

?>

<script>
function cancelTask(id) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "cancel_task.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert('Task canceled!');
            location.reload(); // Reload the page to reflect the changes
        }
    };
    xhr.send("id=" + id);
}

function finishTask(id) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "finish_task.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = xhr.responseText;
            if (response.includes("Task finished successfully")) {
                alert('Task finished!');
                location.reload(); // Reload the page to reflect the changes
            } else {
                alert('Error: ' + response);
            }
        }
    };
    xhr.send("id=" + id);
}
</script>