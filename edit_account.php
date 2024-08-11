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

// Database connection
// Ensure $conn is properly initialized and connected
if ($conn === false) {
    die("ERROR: Could not connect to the database.");
}

// Fetch user data based on email
$stmt = $conn->prepare("SELECT id, name, father_name, address, mobile_number, email, balance, account_type, account_number FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Check if user data was fetched successfully
if (!$user) {
    echo "User data not found.";
    exit();
}

// Update user data if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];
    $email = $_POST['email'];
    $balance = $_POST['balance'];
    $account_type = $_POST['account_type'];
    $account_number = $_POST['account_number'];

    $stmt = $conn->prepare("UPDATE users SET name = ?, father_name = ?, address = ?, mobile_number = ?, email = ?, balance = ?, account_type = ?, account_number = ? WHERE email = ?");
    $stmt->bind_param("sssssssss", $name, $father_name, $address, $mobile_number, $email, $balance, $account_type, $account_number, $email);
    $stmt->execute();
    $stmt->close();

    // Redirect with success message
    header("Location: edit_account.php?success=1");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Account Details</h2>
        
        <!-- Display success message -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success" role="alert" id="success-alert">
                Account details updated successfully!
            </div>
        <?php endif; ?>

        <form method="post" action="edit_account.php" class="needs-validation" novalidate>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your name.</div>
            </div>
            <div class="form-group">
                <label for="father_name">Father's Name:</label>
                <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo htmlspecialchars($user['father_name'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your father's name.</div>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your address.</div>
            </div>
            <div class="form-group">
                <label for="mobile_number">Mobile Number:</label>
                <input type="text" class="form-control" id="mobile_number" name="mobile_number" value="<?php echo htmlspecialchars($user['mobile_number'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your mobile number.</div>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>
            <div class="form-group">
                <label for="balance">Balance:</label>
                <input type="text" class="form-control" id="balance" name="balance" value="<?php echo htmlspecialchars($user['balance'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your balance.</div>
            </div>
            <div class="form-group">
                <label for="account_type">Account Type:</label>
                <input type="text" class="form-control" id="account_type" name="account_type" value="<?php echo htmlspecialchars($user['account_type'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your account type.</div>
            </div>
            <div class="form-group">
                <label for="account_number">Account Number:</label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="<?php echo htmlspecialchars($user['account_number'] ?? ''); ?>" required>
                <div class="invalid-feedback">Please enter your account number.</div>
            </div>
            
            <!-- Button row with Bootstrap classes to align buttons side by side -->
             <!-- Button row with flexbox for closer alignment -->
            <div class="button-container">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="account_management.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Function to hide success alert after 5 seconds
        setTimeout(function() {
            var alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>

