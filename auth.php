<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

// Ensure role is set
$_SESSION['role'] = $_SESSION['role'] ?? 'User';
?>
