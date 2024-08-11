<?php

use PHPUnit\Framework\TestCase;

class CreateAccountTest extends TestCase
{
    private $dbConnection;
    private $userAccount;

    protected function setUp(): void
    {
        // Establish a database connection
        $this->dbConnection = new mysqli('localhost', 'root', '', 'bank_app');
        if ($this->dbConnection->connect_error) {
            $this->fail("Connection failed: " . $this->dbConnection->connect_error);
        }

        // Start a transaction
        $this->dbConnection->begin_transaction();

        $this->userAccount = new UserAccount($this->dbConnection);

        // Drop the users table if it exists to avoid foreign key issues
        $this->dbConnection->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->dbConnection->query("DROP TABLE IF EXISTS users");
        $this->dbConnection->query("SET FOREIGN_KEY_CHECKS = 1");

        // Create the users table without foreign key constraints
        $this->dbConnection->query("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            father_name VARCHAR(255),
            address VARCHAR(255),
            mobile_number VARCHAR(20),
            email VARCHAR(255) UNIQUE,
            password VARCHAR(255),
            balance DECIMAL(10, 2),
            account_type VARCHAR(50),
            account_number VARCHAR(50) UNIQUE,
            bank_name VARCHAR(255)
        )");
    }

    protected function tearDown(): void
    {
        // Rollback the transaction to clean up data
        $this->dbConnection->rollback();

        // Drop the table
        $this->dbConnection->query("SET FOREIGN_KEY_CHECKS = 0");
        $this->dbConnection->query("DROP TABLE IF EXISTS users");
        $this->dbConnection->query("SET FOREIGN_KEY_CHECKS = 1");

        $this->dbConnection->close();
    }

    public function testCreateAccountEmailExists()
    {
        // Insert a record to simulate existing email
        $this->dbConnection->query("INSERT INTO users (name, father_name, address, mobile_number, email, password, balance, account_type, account_number, bank_name) VALUES ('Test User', 'Father Name', 'Address', '1234567890', 'uniqueemail@example.com', 'password', '1000', 'personal', 'U123456789', 'Test Bank')");

        $data = [
            'name' => 'Abdul hanan',
            'father_name' => 'Father Name',
            'address' => 'Abu Dhabi, UAE',
            'mobile_number' => '3240356159',
            'email' => 'uniqueemail@example.com', // Use the same email as above
            'password' => 'P@ssw0rd!',
            'balance' => '30000',
            'account_type' => 'personal',
            'account_number' => 'U987654321', // Ensure unique account number
            'bank_name' => 'United Bank Limited'
        ];

        // Call the method to be tested
        $result = $this->userAccount->createAccount($data);

        // Assert the expected result
        $this->assertEquals("Email already exists. Please use a different email address.", $result);
    }

    public function testCreateAccountSuccess()
    {
        // Ensure a unique email and account number
        $email = 'johndoe' . time() . '@example.com';
        $accountNumber = 'P' . time();

        $data = [
            'name' => 'John Doe',
            'father_name' => 'Father Name',
            'address' => '123 Street',
            'mobile_number' => '1234567890',
            'email' => $email,
            'password' => 'password',
            'balance' => '1000',
            'account_type' => 'savings',
            'account_number' => $accountNumber,
            'bank_name' => 'Bank Name'
        ];

        // Call the method to be tested
        $result = $this->userAccount->createAccount($data);

        // Assert the expected result
        $this->assertEquals("Account Creation successful! Your account number is " . $accountNumber . ".", $result);
    }
}

// Definition of UserAccount class
class UserAccount
{
    private $dbConnection;

    public function __construct($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function createAccount($data)
    {
        // Extract data
        $name = $data['name'];
        $father_name = $data['father_name'];
        $address = $data['address'];
        $mobile_number = $data['mobile_number'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $balance = $data['balance'];
        $account_type = $data['account_type'];
        $account_number = $data['account_number'];
        $bank_name = $data['bank_name'];

        // Check if email already exists
        $emailCheckStmt = $this->dbConnection->prepare("SELECT id FROM users WHERE email = ?");
        $emailCheckStmt->bind_param("s", $email);
        $emailCheckStmt->execute();
        $emailCheckStmt->store_result();

        if ($emailCheckStmt->num_rows > 0) {
            // Email already exists
            return "Email already exists. Please use a different email address.";
        } else {
            // Prepare and bind
            $stmt = $this->dbConnection->prepare("INSERT INTO users (name, father_name, address, mobile_number, email, password, balance, account_type, account_number, bank_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $name, $father_name, $address, $mobile_number, $email, $password, $balance, $account_type, $account_number, $bank_name);

            if ($stmt->execute()) {
                // Registration successful
                return "Account Creation successful! Your account number is " . $account_number . ".";
            } else {
                // Error handling
                return "Error: " . $stmt->error;
            }
        }
    }
}
