<?php

use PHPUnit\Framework\TestCase;

class TransferFundsTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli('localhost', 'root', '', 'bank_app');
        if ($this->conn->connect_error) {
            die('Connection failed: ' . $this->conn->connect_error);
        }

        // Disable foreign key checks
        $this->conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Clean up existing test data
        $this->conn->query("DELETE FROM users WHERE email IN ('john@example.com', 'jane@example.com')");
        $this->conn->query("DELETE FROM auth_tokens WHERE user_id IN (1, 2)");

        // Start a transaction
        $this->conn->begin_transaction();

        // Insert test data with unique values
        $this->conn->query("INSERT INTO users (email, name, account_number, balance) VALUES ('john@example.com', 'John Doe', '123456', 1000)");
        $this->conn->query("INSERT INTO users (email, name, account_number, balance) VALUES ('jane@example.com', 'Jane Doe', '654321', 500)");
        $this->conn->query("INSERT INTO auth_tokens (user_id, token) VALUES (1, 'testtoken1')");
        $this->conn->query("INSERT INTO auth_tokens (user_id, token) VALUES (2, 'testtoken2')");

        // Re-enable foreign key checks
        $this->conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    protected function tearDown(): void
    {
        // Rollback the transaction
        $this->conn->rollback();

        $this->conn->close();
    }

    public function testTransferFundsSuccess()
    {
        $_POST['recipient_account'] = '654321';
        $_POST['transfer_amount'] = '100';
        $_SESSION['email'] = 'john@example.com';

        ob_start();
        include 'fundstransfer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Funds transferred successfully!', $output);

        $result = $this->conn->query("SELECT balance FROM users WHERE email = 'john@example.com'");
        $johnBalance = $result->fetch_assoc()['balance'];

        $result = $this->conn->query("SELECT balance FROM users WHERE account_number = '654321'");
        $janeBalance = $result->fetch_assoc()['balance'];

        $this->assertEquals(900.00, $johnBalance);
        $this->assertEquals(600.00, $janeBalance);
    }

    public function testInvalidRecipient()
    {
        $_POST['recipient_account'] = '000000';
        $_POST['transfer_amount'] = '100';
        $_SESSION['email'] = 'john@example.com';

        ob_start();
        include 'fundstransfer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Recipient account number is invalid.', $output);
    }

    public function testInsufficientFunds()
    {
        $_POST['recipient_account'] = '654321';
        $_POST['transfer_amount'] = '2000';
        $_SESSION['email'] = 'john@example.com';

        ob_start();
        include 'fundstransfer.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Please enter a valid amount not exceeding your current balance.', $output);
    }
}
