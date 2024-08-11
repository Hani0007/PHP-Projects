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
$stmt = $conn->prepare("SELECT name, account_number, balance FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($name, $account_number, $balance);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_account = $_POST['recipient_account'];
    $transfer_amount = $_POST['transfer_amount'];

    // Validate transfer amount
    if (!is_numeric($transfer_amount) || $transfer_amount <= 0 || $transfer_amount > $balance) {
        $error_message = "Please enter a valid amount not exceeding your current balance.";
    } else {
        // Fetch recipient details
        $stmt = $conn->prepare("SELECT balance FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $recipient_account);
        $stmt->execute();
        $stmt->bind_result($recipient_balance);
        $stmt->fetch();
        $stmt->close();

        if ($recipient_balance === null) {
            $error_message = "Recipient account number is invalid.";
        } else {
            // Update sender's balance
            $new_balance = $balance - $transfer_amount;
            $updateStmt = $conn->prepare("UPDATE users SET balance = ? WHERE email = ?");
            $updateStmt->bind_param("ds", $new_balance, $email);

            if ($updateStmt->execute()) {
                // Update recipient's balance
                $new_recipient_balance = $recipient_balance + $transfer_amount;
                $updateRecipientStmt = $conn->prepare("UPDATE users SET balance = ? WHERE account_number = ?");
                $updateRecipientStmt->bind_param("ds", $new_recipient_balance, $recipient_account);

                if ($updateRecipientStmt->execute()) {
                    $success_message = "Funds transferred successfully!";
                    $balance = $new_balance; // Update local balance variable

                    // Record the transaction
                    $insertStmt = $conn->prepare("INSERT INTO transactions (sender_account, recipient_account, amount, transaction_type, created_at) VALUES (?, ?, ?, 'Transfer', NOW())");
                    $insertStmt->bind_param("ssd", $account_number, $recipient_account, $transfer_amount);
                    $insertStmt->execute();
                    $insertStmt->close();
                } else {
                    $error_message = "Error updating recipient's balance: " . $updateRecipientStmt->error;
                }
                $updateRecipientStmt->close();
            } else {
                $error_message = "Error updating your balance: " . $updateStmt->error;
            }
            $updateStmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Transfer Funds</h2>
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
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
                <label for="balance">Current Balance:</label>
                <input type="text" class="form-control" id="balance" value="<?php echo htmlspecialchars($balance); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="recipient_account">Recipient Account Number:</label>
                <input type="text" class="form-control" id="recipient_account" name="recipient_account" required>
            </div>
            <div class="form-group">
                <label for="transfer_amount">Amount to Transfer:</label>
                <input type="number" class="form-control" id="transfer_amount" name="transfer_amount" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Transfer Funds</button>
        </form>
        <a href="transactions.php" class="btn btn-secondary mt-3">Back</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
