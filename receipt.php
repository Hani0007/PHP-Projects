<?php
include "db_connection.php";
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get user email from session
$email = $_SESSION['email'];

// Fetch user details from the database
$stmt = $conn->prepare("SELECT name, account_number, account_type, balance FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $account_number, $account_type, $balance);
$stmt->fetch();
$stmt->close();

// Fetch the latest transaction details from the deposit table
$transStmt = $conn->prepare("SELECT transaction_type, created_at, deposited_amount FROM deposit WHERE account_number = ? ORDER BY created_at DESC LIMIT 1");
$transStmt->bind_param("s", $account_number);
$transStmt->execute();
$transStmt->bind_result($transaction_type, $created_at, $deposited_amount);
$transStmt->fetch();
$transStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Receipt</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Transaction Details</h5>
                <p class="card-text"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
                <p class="card-text"><strong>Account Number:</strong> <?php echo htmlspecialchars($account_number); ?></p>
                <p class="card-text"><strong>Account Type:</strong> <?php echo htmlspecialchars($account_type); ?></p>
                <p class="card-text"><strong>Transaction Type:</strong> <?php echo htmlspecialchars($transaction_type); ?></p>
                <p class="card-text"><strong>Transaction Date:</strong> <?php echo htmlspecialchars($created_at); ?></p>
                <p class="card-text"><strong>Deposited Amount:</strong> <?php echo htmlspecialchars($deposited_amount); ?></p>
                <p class="card-text"><strong>Current Balance:</strong> <?php echo htmlspecialchars($balance); ?></p>
            </div>
        </div>
        <button onclick="window.print()" class="btn btn-primary mt-3">Print Receipt</button>
        <a href="transactions.php" class="btn btn-secondary mt-3">Back</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>