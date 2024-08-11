<?php
include "db_connection.php";
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$status_message = '';
$payment_info = [];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_type = $_POST['payment_type'];
    $details = $_POST['details'];
    $amount = $_POST['amount'];
    $setup_datetime = $_POST['setup_datetime'];

    // Validate input
    if (empty($payment_type) || empty($details) || empty($amount) || empty($setup_datetime)) {
        $status_message = '<div class="alert alert-danger" role="alert">All fields are required!</div>';
    } else {
        // Check if the payment type already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM recurring_payments WHERE payment_type = ?");
        $stmt->bind_param("s", $payment_type);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();

        if ($row[0] > 0) {
            $status_message = '<div class="alert alert-danger" role="alert">You have already created a recurring payment with this type. Please use a different one.</div>';
        } else {
            // Insert into the recurring_payments table
            $stmt = $conn->prepare("INSERT INTO recurring_payments (payment_type, details, amount, setup_datetime) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $payment_type, $details, $amount, $setup_datetime);

            if ($stmt->execute()) {
                // Fetch the recently added recurring payment
                $last_id = $stmt->insert_id;
                $stmt = $conn->prepare("SELECT payment_type, details, amount, setup_datetime FROM recurring_payments WHERE id = ?");
                $stmt->bind_param("i", $last_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $payment_info = $result->fetch_assoc();
                    $status_message = '<div class="alert alert-success" role="alert">Recurring payment set up successfully!</div>';
                } else {
                    $status_message = '<div class="alert alert-warning" role="alert">Failed to retrieve the payment information.</div>';
                }
            } else {
                $status_message = '<div class="alert alert-danger" role="alert">Error: ' . $stmt->error . '</div>';
            }

            $stmt->close();
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
    <title>Recurring Payment Processed</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Recurring Payment Processed</h2>
        <?php echo $status_message; ?>
        <?php if (!empty($payment_info)): ?>
            <div class="mt-5">
                <h3>Payment Details</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Payment Type</th>
                        <td><?php echo htmlspecialchars($payment_info['payment_type']); ?></td>
                    </tr>
                    <tr>
                        <th>Details</th>
                        <td><?php echo htmlspecialchars($payment_info['details']); ?></td>
                    </tr>
                    <tr>
                        <th>Amount</th>
                        <td><?php echo htmlspecialchars($payment_info['amount']); ?></td>
                    </tr>
                    <tr>
                        <th>Setup Date and Time</th>
                        <td><?php echo htmlspecialchars($payment_info['setup_datetime']); ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
        <a href="recurring_payments.php" class="btn btn-secondary mt-3">Back</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
