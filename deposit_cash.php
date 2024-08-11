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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_amount = $_POST['transaction_amount'];
    $transaction_type = $_POST['transaction_type'];

    // Validate transaction amount
    if (!is_numeric($transaction_amount) || $transaction_amount <= 0) {
        $error_message = "Please enter a valid amount greater than zero.";
    } else {
        // Determine new balance based on transaction type
        if ($transaction_type == "Deposit") {
            $new_balance = $balance + $transaction_amount;
        } elseif ($transaction_type == "Withdraw") {
            $new_balance = $balance - $transaction_amount;
        }

        $updateStmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
        $updateStmt->bind_param("ds", $new_balance, $email);

        if ($updateStmt->execute()) {
            // Insert transaction details into the deposit table
            $insertStmt = $conn->prepare("INSERT INTO deposit (name, account_number, account_type, transaction_type, created_at, deposited_amount) VALUES (?, ?, ?, ?, NOW(), ?)");
            $insertStmt->bind_param("ssssd", $name, $account_number, $account_type, $transaction_type, $transaction_amount);
            $insertStmt->execute();
            $insertStmt->close();

            $success_message = "Balance updated successfully!";
            $balance = $new_balance; // Update local balance variable
        } else {
            $error_message = "Error updating balance: " . $updateStmt->error;
        }

        $updateStmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Cash</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Deposit Cash</h2>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($name); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="account_number">Your Account Number:</label>
                <input type="text" class="form-control" id="account_number" value="<?php echo htmlspecialchars($account_number); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="account_type">Account Type:</label>
                <input type="text" class="form-control" id="account_type" value="<?php echo htmlspecialchars($account_type); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="transaction_type">Transaction Type:</label>
                <select class="form-control" id="transaction_type" name="transaction_type" required>
                    <option value="Deposit">Deposit</option>
                    <option value="Withdraw">Withdraw</option>
                </select>
            </div>
            <div class="form-group">
                <label for="transaction_amount">Money you want to deposit:</label>
                <input type="number" class="form-control" id="transaction_amount" name="transaction_amount" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Balance</button>
        </form>
        <a href="transactions.php" class="btn btn-secondary mt-3">Back</a>
        <a href="receipt.php" class="btn btn-secondary mt-3">View Receipt</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
