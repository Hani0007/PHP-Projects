<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
// Check if the user is logged in
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// Get user name from session
// $user_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Services</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Account Services</h2>
        <div class="row mt-4">
            <div class="col-md-6">
                <!-- Button to manage checkbooks and debit/credit cards -->
                 <a href="account_information.php" class="btn btn-primary btn-block mb-3">Get Account Information</a>
                <a href="manage_services.php" class="btn btn-primary btn-block mb-3">Request and Manage Services (Checkbooks, Debit/Credit Cards)</a>
                <!-- Button to set up and manage recurring payments and direct debits -->
                <a href="recurring_payments.php" class="btn btn-primary btn-block mb-3">Set Up and Manage Recurring Payments and Direct Debits</a>
                <a href="dashboard.php" class="btn btn-secondary btn-block">Back</a>
                <a href="logout.php" class="btn btn-danger btn-block">Logout</a>
            </div>
        </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
