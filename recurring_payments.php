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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Recurring Payments</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Set Up Recurring Payments</h2>

        <form action="process_recurring_payment.php" method="post">
            <div class="form-group">
                <label for="payment_type">Payment Type:</label>
                <select class="form-control" id="payment_type" name="payment_type" required>
                    <option value="">Select a payment type</option>
                    <option value="Utility Bill">Utility Bill</option>
                    <option value="Subscription">Subscription</option>
                    <option value="Loan">Loan</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="details">Details:</label>
                <textarea class="form-control" id="details" name="details" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="setup_datetime">Setup Date and Time:</label>
                <input type="datetime-local" class="form-control" id="setup_datetime" name="setup_datetime" required>
            </div>
            <button type="submit" class="btn btn-primary">Set Up Recurring Payment</button>
        </form>

        <a href="account_services.php" class="btn btn-secondary mt-3">Back</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
