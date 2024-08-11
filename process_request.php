<?php
include "db_connection.php";
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$status_message = '';
$requests = [];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_type = $_POST['service_type'];
    $additional_details = $_POST['additional_details'];

    // Validate input
    if (empty($service_type) || empty($additional_details)) {
        $status_message = '<div class="alert alert-danger" role="alert">All fields are required!</div>';
    } else {
        // Check if the request already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM service_requests WHERE service_type = ? AND additional_details = ?");
        $checkStmt->bind_param("ss", $service_type, $additional_details);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $count = $checkResult->fetch_row()[0];
        $checkStmt->close();

        if ($count > 0) {
            $status_message = '<div class="alert alert-warning" role="alert">Your request is already in process. You cannot make the same request again.</div>';
        } else {
            // Insert into the table
            $stmt = $conn->prepare("INSERT INTO service_requests (service_type, additional_details, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $service_type, $additional_details);

            if ($stmt->execute()) {
                $status_message = '<div class="alert alert-success" role="alert">Service request submitted successfully!</div>';
            } else {
                $status_message = '<div class="alert alert-danger" role="alert">Error: ' . $stmt->error . '</div>';
            }

            $stmt->close();
        }
    }
}

// Fetch previous requests
$requestStmt = $conn->prepare("SELECT service_type, additional_details, created_at FROM service_requests");
$requestStmt->execute();
$result = $requestStmt->get_result();

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

$requestStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request and Manage Services</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Request and Manage Services</h2>
        <?php echo $status_message; ?>
        <form action="process_request.php" method="post">
            <div class="form-group">
                <label for="service_type">Select Service:</label>
                <select class="form-control" id="service_type" name="service_type" required>
                    <option value="">Select a service</option>
                    <option value="Checkbook">Checkbook</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Credit Card">Credit Card</option>
                </select>
            </div>
            <div class="form-group">
                <label for="additional_details">Additional Details:</label>
                <textarea class="form-control" id="additional_details" name="additional_details" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Apply Service</button>
        </form>
        <a href="account_services.php" class="btn btn-secondary mt-3">Back</a>

        <!-- Display previous requests -->
        <h3 class="mt-5">Previous Requests</h3>
        <?php if (count($requests) > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Service Type</th>
                        <th>Additional Details</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['service_type']); ?></td>
                            <td><?php echo htmlspecialchars($request['additional_details']); ?></td>
                            <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No previous requests found.</p>
        <?php endif; ?>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
