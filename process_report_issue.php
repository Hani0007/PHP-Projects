<?php
include "db_connection.php";
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$status_message = '';
$issue_info = [];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $duration = $_POST['duration'];

    // Validate input
    if (empty($title) || empty($description) || empty($duration)) {
        $status_message = '<div class="alert alert-danger" role="alert">All fields are required!</div>';
    } else {
        // Insert into the issues table
        $stmt = $conn->prepare("INSERT INTO issues (title, description, duration) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $duration);

        if ($stmt->execute()) {
            // Fetch the recently added issue
            $last_id = $stmt->insert_id;
            $stmt = $conn->prepare("SELECT title, description, duration, created_at FROM issues WHERE id = ?");
            $stmt->bind_param("i", $last_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $issue_info = $result->fetch_assoc();
                $status_message = '<div class="alert alert-success" role="alert">Your issue is being reported by our technical team. We will make sure to resolve this.</div>';
            } else {
                $status_message = '<div class="alert alert-warning" role="alert">Failed to retrieve the issue information.</div>';
            }
        } else {
            $status_message = '<div class="alert alert-danger" role="alert">Error: ' . $stmt->error . '</div>';
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
    <title>Issue Reported</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Bootstrap JavaScript for interactive components -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Issue Reported</h2>
        <?php echo $status_message; ?>
        <?php if (!empty($issue_info)): ?>
            <div class="mt-5">
                <h3>Issue Details</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Title</th>
                        <td><?php echo htmlspecialchars($issue_info['title']); ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?php echo htmlspecialchars($issue_info['description']); ?></td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td><?php echo htmlspecialchars($issue_info['duration']); ?></td>
                    </tr>
                    <tr>
                        <th>Reported At</th>
                        <td><?php echo htmlspecialchars($issue_info['created_at']); ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>
        <a href="report_issue.php" class="btn btn-secondary mt-3">Back</a>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
