<?php
session_start();
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
    error_log('Database connection failed: ' . $conn->connect_error);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$stmt = $conn->prepare("SELECT is_admin FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $user_username, $user_password);

// Execute the statement
if (!$stmt->execute()) {
    error_log('Statement execution failed: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}

$result = $stmt->get_result();

// Initialize the variables to null
$stmt_rescuer = null;
$stmt_citizen = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $is_admin = $row['is_admin'];

    $_SESSION['username'] = $user_username;

    if ($is_admin) {
        $_SESSION['role'] = 'admin';
        echo json_encode(['success' => true, 'role' => 'admin']);
    } else {
        $stmt_rescuer = $conn->prepare("SELECT username FROM rescuers WHERE username = ?");
        $stmt_rescuer->bind_param("s", $user_username);
        $stmt_rescuer->execute();
        $result_rescuer = $stmt_rescuer->get_result();

        if ($result_rescuer->num_rows > 0) {
            $_SESSION['role'] = 'rescuer';
            echo json_encode(['success' => true, 'role' => 'rescuer']);
        } else {
            $stmt_citizen = $conn->prepare("SELECT username FROM citizens WHERE username = ?");
            $stmt_citizen->bind_param("s", $user_username);
            $stmt_citizen->execute();
            $result_citizen = $stmt_citizen->get_result();

            if ($result_citizen->num_rows > 0) {
                $_SESSION['role'] = 'citizen';
                echo json_encode(['success' => true, 'role' => 'citizen']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Role not found']);
            }
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

// Close the statements and connection
$stmt->close();
if (isset($stmt_rescuer) && $stmt_rescuer !== null) {
    $stmt_rescuer->close();
}
if (isset($stmt_citizen) && $stmt_citizen !== null) {
    $stmt_citizen->close();
}
$conn->close();
?>