<?php
session_start();

function check_login($required_role = null) {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        // User is not logged in
        header('Location: login.html');
        exit;
    }

    if ($required_role && $_SESSION['role'] !== $required_role) {
        // User does not have the required role
        header('Location: unauthorized.html');
        exit;
    }
}
?>
