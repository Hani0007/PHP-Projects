<?php

use PHPUnit\Framework\TestCase;

class DepositCashTest extends TestCase
{
    private $dbConnection;
    private $session;
    private $postData;

    protected function setUp(): void
    {
        // Mock the database connection
        $this->dbConnection = $this->createMock(mysqli::class);

        // Mock the session
        $this->session = [
            'email' => 'test@example.com',
        ];
    }

    private function createDepositCashScript()
    {
        return new class($this->dbConnection, $this->session, $this->postData) {
            private $dbConnection;
            private $session;
            private $postData;

            public function __construct($dbConnection, $session, $postData)
            {
                $this->dbConnection = $dbConnection;
                $this->session = $session;
                $this->postData = $postData;
            }

            public function process()
            {
                // Check if user is logged in
                if (!isset($this->session['email'])) {
                    return "User not logged in.";
                }

                $email = $this->session['email'];

                // Fetch user details from the database
                $stmt = $this->dbConnection->prepare("SELECT name, account_number, account_type, created_at, balance FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($name, $account_number, $account_type, $created_at, $balance);
                $stmt->fetch();
                $stmt->close();

                if (!isset($this->postData['transaction_amount']) || !isset($this->postData['transaction_type'])) {
                    return "No POST data received.";
                }

                $transaction_amount = $this->postData['transaction_amount'];
                $transaction_type = $this->postData['transaction_type'];

                // Validate transaction amount
                if (!is_numeric($transaction_amount) || $transaction_amount <= 0) {
                    return "Please enter a valid amount greater than zero.";
                } else {
                    // Determine new balance based on transaction type
                    if ($transaction_type == "Deposit") {
                        $new_balance = $balance + $transaction_amount;
                    } elseif ($transaction_type == "Withdraw") {
                        $new_balance = $balance - $transaction_amount;
                    }

                    $updateStmt = $this->dbConnection->prepare("UPDATE users SET balance = ? WHERE email = ?");
                    $updateStmt->bind_param("ds", $new_balance, $email);

                    if ($updateStmt->execute()) {
                        // Insert transaction details into the deposit table
                        $insertStmt = $this->dbConnection->prepare("INSERT INTO deposit (name, account_number, account_type, transaction_type, created_at, deposited_amount) VALUES (?, ?, ?, ?, NOW(), ?)");
                        $insertStmt->bind_param("ssssd", $name, $account_number, $account_type, $transaction_type, $transaction_amount);
                        $insertStmt->execute();
                        $insertStmt->close();

                        $updateStmt->close();
                        return "Balance updated successfully!";
                    } else {
                        $updateStmt->close();
                        return "Error updating balance: " . $updateStmt->error;
                    }
                }
            }
        };
    }

    public function testValidDeposit()
    {
        // Mock the database statements
        $selectStmt = $this->createMock(mysqli_stmt::class);
        $selectStmt->method('bind_param')->willReturn(true);
        $selectStmt->method('execute')->willReturn(true);
        $selectStmt->method('bind_result')->willReturn(true);
        $selectStmt->method('fetch')->willReturn(true);
        $selectStmt->method('close')->willReturn(true);

        $updateStmt = $this->createMock(mysqli_stmt::class);
        $updateStmt->method('bind_param')->willReturn(true);
        $updateStmt->method('execute')->willReturn(true);
        $updateStmt->method('close')->willReturn(true);

        $insertStmt = $this->createMock(mysqli_stmt::class);
        $insertStmt->method('bind_param')->willReturn(true);
        $insertStmt->method('execute')->willReturn(true);
        $insertStmt->method('close')->willReturn(true);

        // Configure the database connection to return the mock statements
        $this->dbConnection->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnMap([
                ["SELECT name, account_number, account_type, created_at, balance FROM users WHERE email = ?", $selectStmt],
                ["UPDATE users SET balance = ? WHERE email = ?", $updateStmt],
                ["INSERT INTO deposit (name, account_number, account_type, transaction_type, created_at, deposited_amount) VALUES (?, ?, ?, ?, NOW(), ?)", $insertStmt],
            ]);

        // Mock the POST data
        $this->postData = [
            'transaction_amount' => 100.00,
            'transaction_type' => 'Deposit'
        ];

        // Create the DepositCashScript instance and process the data
        $script = $this->createDepositCashScript();
        $result = $script->process();

        // Assert success message
        $this->assertEquals("Balance updated successfully!", $result);
    }

    public function testInvalidDepositAmount()
    {
        // Mock the database statements
        $selectStmt = $this->createMock(mysqli_stmt::class);
        $selectStmt->method('bind_param')->willReturn(true);
        $selectStmt->method('execute')->willReturn(true);
        $selectStmt->method('bind_result')->willReturn(true);
        $selectStmt->method('fetch')->willReturn(true);
        $selectStmt->method('close')->willReturn(true);

        // Configure the database connection to return the mock statements
        $this->dbConnection->expects($this->once())
            ->method('prepare')
            ->willReturn($selectStmt);

        // Mock the POST data
        $this->postData = [
            'transaction_amount' => 0, // Invalid amount
            'transaction_type' => 'Deposit'
        ];

        // Create the DepositCashScript instance and process the data
        $script = $this->createDepositCashScript();
        $result = $script->process();

        // Assert error message
        $this->assertEquals("Please enter a valid amount greater than zero.", $result);
    }
}
