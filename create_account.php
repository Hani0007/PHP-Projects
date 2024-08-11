<?php
include "db_connection.php";
session_start(); // Start the session if not already started

if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit();
}

// Initialize variables
$name = $father_name = $address = $mobile_number = $email = $password = $balance = $account_type = $account_number = $bank_name = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $father_name = $_POST['father_name'];
    $address = $_POST['address'];
    $mobile_number = $_POST['mobile_number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $balance = $_POST['balance'];
    $account_type = $_POST['account_type'];
    $account_number = $_POST['account_number']; // Get generated account number from form
    $bank_name = $_POST['bank_name']; // Get bank name from form

    // Check if email already exists
    $emailCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $emailCheckStmt->store_result();

    if ($emailCheckStmt->num_rows > 0) {
        // Email already exists
        echo '<script>
                alert("Email already exists. Please use a different email address.");
                window.location.href = "create_account.php"; // Redirect back to registration page
              </script>';
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (name, father_name, address, mobile_number, email, password, balance, account_type, account_number, bank_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $name, $father_name, $address, $mobile_number, $email, $password, $balance, $account_type, $account_number, $bank_name);

        if ($stmt->execute()) {
            // Registration successful
            echo '<script>
                    alert("Account Creation successful! Your account number is ' . $account_number . '");
                    window.location.href = "login.php"; // Redirect to login page
                  </script>';
        } else {
            // Error handling
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $emailCheckStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Bank Account</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required for dynamic account number generation and validation) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Create New Bank Account</h2>
        <form action="" method="post" onsubmit="return validateForm()" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="father_name">Father's Name:</label>
                        <input type="text" class="form-control" id="father_name" name="father_name" required>
                        <div class="invalid-feedback">Please enter your father's name.</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea class="form-control" id="address" name="address" rows="4" required></textarea>
                <div class="invalid-feedback">Please enter your address.</div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number:</label>
                        <input type="text" class="form-control" id="mobile_number" name="mobile_number" required pattern="[0-9]{10}" title="Please enter 10 digits">
                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                        title="Password must contain at least 8 characters, including uppercase, lowercase letters, numbers, and special characters (@$!%*?&)">
                        <div class="invalid-feedback">Password must contain at least 8 characters, including uppercase, lowercase letters, numbers, and special characters.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="balance">Initial Balance:</label>
                        <input type="number" class="form-control" id="balance" name="balance" required>
                        <div class="invalid-feedback">Please enter your initial balance.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="account_type">Account Type:</label>
                        <select class="form-control" id="account_type" name="account_type" required onchange="generateAccountNumber()">
                            <option value="">Select Account Type</option>
                            <option value="personal">Personal</option>
                            <option value="business">Business</option>
                        </select>
                        <div class="invalid-feedback">Please select your account type.</div>
                    </div>
                </div>
            </div>

            <!-- Account Number field (readonly) -->
            <div class="form-group">
                <label for="account_number">Account Number:</label>
                <input type="text" class="form-control" id="account_number" name="account_number" readonly>
                <div class="invalid-feedback">Please generate an account number.</div>
            </div>

            <!-- New Bank Name field -->
            <div class="form-group">
                <label for="bank_name">Bank Name:</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" required>
                <div class="invalid-feedback">Please enter the bank name.</div>
            </div>

            <div class="form-row">
                <div class="col">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
                <div class="col text-right">
                    <p class="mt-2 mb-0">Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </form>
    </div>


    <script>
        // Function to generate account number based on account type
        function generateAccountNumber() {
            var accountType = document.getElementById("account_type").value;
            var randomNumber = Math.floor(1000000000 + Math.random() * 9000000000); // Generate 10-digit random number

            var prefix = "";
            switch (accountType) {
                case "personal":
                    prefix = "P";
                    break;
                case "business":
                    prefix = "B";
                    break;
                default:
                    prefix = "U"; // Unknown type
                    break;
            }

            var generatedAccountNumber = prefix + randomNumber;
            document.getElementById("account_number").value = generatedAccountNumber;
        }

        // Function to validate form inputs
        function validateForm() {
            var form = document.querySelector('.needs-validation');
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');

            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            if (password !== confirmPassword) {
                document.getElementById("confirm_password").setCustomValidity("Passwords do not match.");
                return false;
            } else {
                document.getElementById("confirm_password").setCustomValidity("");
            }

            return true;
        }
    </script>
</body>
</html>
