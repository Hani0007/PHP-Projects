<?php
include "db_connection.php";
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$status_message = '';
$user_info = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_number = $_POST['account_number'];

   
    if (empty($account_number)) {
        $status_message = '<div class="alert alert-danger" role="alert">Account number is required!</div>';
    } else {

        $stmt = $conn->prepare("SELECT name, father_name, address, mobile_number, email, balance, account_type, bank_name FROM users WHERE account_number = ?");
        $stmt->bind_param("s", $account_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_info = $result->fetch_assoc();
        } else {
            $status_message = '<div class="alert alert-warning" role="alert">No account found with the provided number.</div>';
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
 
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Get Account Information</h2>
        <?php echo $status_message; ?>
        <form action="account_information.php" method="post">
            <div class="form-group">
                <label for="account_number">Enter Account Number:</label>
                <input type="text" class="form-control" id="account_number" name="account_number" required>

            </div>
            <button type="submit" class="btn btn-primary">Get Information</button>


        </form>

        <?php if (!empty($user_info)): ?>
            <div class="mt-5">
                <h3>Account Details</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Name</th>
                        <td><?php echo htmlspecialchars($user_info['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Father's Name</th>
                        <td><?php echo htmlspecialchars($user_info['father_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td><?php echo htmlspecialchars($user_info['address']); ?></td>
                    </tr>
                    <tr>
                        <th>Mobile Number</th>
                        <td><?php echo htmlspecialchars($user_info['mobile_number']); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo htmlspecialchars($user_info['email']); ?></td>
                    </tr>
                    <tr>
                        <th>Balance</th>
                        <td><?php echo htmlspecialchars($user_info['balance']); ?></td>
                    </tr>
                    <tr>
                        <th>Account Type</th>
                        <td><?php echo htmlspecialchars($user_info['account_type']); ?></td>
                    </tr>
                    <tr>
                        <th>Bank Name</th>
                        <td><?php echo htmlspecialchars($user_info['bank_name']); ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
         
        <a href="account_services.php" class="btn btn-secondary mt-3">Back</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
