<?php
session_start();
include "db_connection.php";

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user email from session
$user_email = $_SESSION['email'];

// Delete the user record from the database
$sql = "DELETE FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);

if ($stmt->execute()) {
    // Successfully deleted, log out the user
    session_destroy();
    header("Location: confirmation.php?message=account_closed");
    exit();
} else {
    // Redirect to confirmation page with error message
    header("Location: confirmation.php?message=error");
    exit();
}

$stmt->close();
$conn->close();
?>
