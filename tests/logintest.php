<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPUnit\Framework\TestCase;

require 'vendor/autoload.php'; // Ensure PHPUnit and PHPMailer are autoloaded

// Mock the sendMail function
function sendMail($to, $subject, $body) {
    return true; // Mocking success for the test
}

// Mocked Database Connection
class MockedMysqli {
    public function __construct() {}

    public function prepare($query) {
        return new MockedStatement();
    }

    public function connect_error() {
        return false; // Simulate no connection error
    }

    public function close() {}
}

class MockedStatement {
    public function bind_param($type, $param) {}
    public function execute() {}
    public function bind_result(&$id, &$password) {
        // Simulate the result set for a successful login
        $id = 1;
        $password = password_hash('password', PASSWORD_DEFAULT); // Adjust as needed
    }
    public function fetch() {
        return true; // Simulate successful fetch
    }
    public function close() {}
}

// Login script
function login($email, $password) {
    // Use the mocked mysqli class
    $conn = new MockedMysqli();

    // Simulate the method call for connection error
    if ($conn->connect_error()) {
        return "Connection failed.";
    }

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

                // Generate a 2FA code using the secret key
                $two_factor_code = rand(100000, 999999);
                $_SESSION['two_factor_code'] = $two_factor_code;
                $_SESSION['two_factor_user_id'] = $id;
                $_SESSION['email'] = $email;

                // Send the 2FA code to the user via email
                $subject = "Your 2FA Code";
                $message = "Your 2FA code is: " . $two_factor_code;
                if (sendMail($email, $subject, $message)) {
                    return "Success";
                } else {
                    return "Failed to send the 2FA code. Please try again later.";
                }
            } else {
                return "Incorrect password.";
            }
        } else {
            return "No account found with that email.";
        }
        $stmt->close();
    }
    $conn->close();
}

// PHPUnit Test
class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear session before each test
        $_SESSION = [];
    }

    public function testLoginSuccess()
    {
        // Mock a successful database interaction
        $result = login('test@example.com', 'password');
        $this->assertEquals("Success", $result);
    }

    public function testLoginFailure()
    {
        // Mock a failed database interaction
        $result = login('abdulhanaaan123@gmail.com', 'wrongpassword');
        $this->assertEquals("Incorrect password.", $result);
    }
}

?>
