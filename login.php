<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vasi";

$input = json_decode(file_get_contents('php://input'), true);
$user_username = $input['username'];
$user_password = $input['password'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $user_username, $user_password);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $is_admin = $row['is_admin'];

    if ($is_admin) {
        echo json_encode(['success' => true, 'role' => 'admin']);
    } else {
        $stmt_rescuer = $conn->prepare("SELECT username FROM rescuers WHERE username = ?");
        $stmt_rescuer->bind_param("s", $user_username);
        $stmt_rescuer->execute();
        $result_rescuer = $stmt_rescuer->get_result();

        if ($result_rescuer->num_rows > 0) {
            echo json_encode(['success' => true, 'role' => 'rescuer']);
        } else {
            $stmt_citizen = $conn->prepare("SELECT username FROM citizens WHERE username = ?");
            $stmt_citizen->bind_param("s", $user_username);
            $stmt_citizen->execute();
            $result_citizen = $stmt_citizen->get_result();

            if ($result_citizen->num_rows > 0) {
                echo json_encode(['success' => true, 'role' => 'citizen']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Role not found']);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

$stmt->close();
$stmt_rescuer->close();
$stmt_citizen->close();
$conn->close();
?>