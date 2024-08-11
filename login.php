<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

include "db_connection.php";



function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'abdulhanaaan123@gmail.com'; // Your SMTP username
        $mail->Password = 'vrcr iibl xcyr ecap'; // Your SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('abdulhanaaan123@gmail.com', 'Your Name');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email)) {
        $email_error = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $password_error = "Please enter your password.";
    }

    if (empty($email_error) && empty($password_error)) {
        $query = "SELECT id, password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($id, $hashed_password);
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    // Generate a random secret key
                    $secret_key = bin2hex(random_bytes(16));
                    $_SESSION['secret_key'] = $secret_key;

                    // Display the secret key in a Bootstrap alert
                    echo '<div class="alert alert-info" role="alert">
                            Your secret key is: <strong>' . $secret_key . '</strong>
                          </div>';

                    // Generate a 2FA code using the secret key
                    $two_factor_code = rand(100000, 999999);
                    $_SESSION['two_factor_code'] = $two_factor_code;
                    $_SESSION['two_factor_user_id'] = $id;
                    $_SESSION['email'] = $email;

                    // Send the 2FA code to the user via email
                    $subject = "Your 2FA Code";
                    $message = "Your 2FA code is: " . $two_factor_code;
                    if (sendMail($email, $subject, $message)) {
                        // Redirect to the 2FA verification page
                        header("Location: verify_2fa.php");
                        exit();
                    } else {
                        echo '<div class="alert alert-danger" role="alert">
                                Failed to send the 2FA code. Please try again later.
                              </div>';
                    }
                } else {
                    $password_error = "Incorrect password.";
                }
            } else {
                $email_error = "No account found with that email.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Login</h2>
                <form action="" method="post" onsubmit="return validateForm()" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control <?php echo isset($email_error) ? 'is-invalid' : ''; ?>" id="email" name="email" required>
                        <div class="invalid-feedback">
                            <?php echo isset($email_error) ? htmlspecialchars($email_error) : 'Please enter a valid email address.'; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control <?php echo isset($password_error) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                        <div class="invalid-feedback">
                            <?php echo isset($password_error) ? htmlspecialchars($password_error) : 'Please enter your password.'; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                    <div class="mt-3 text-center">
                        <p class="mb-0">Don't have an account? <a href="create_account.php">Create New Account</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function validateForm() {
            var form = document.querySelector('.needs-validation');
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
            return form.checkValidity();
        }
    </script>
</body>
</html>
