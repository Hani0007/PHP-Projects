<?php
// Check if a message is set in the query parameters
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <?php if ($message === 'account_closed'): ?>
            <div class="alert alert-success" role="alert">
                Your account has been successfully closed. You can now <a href="login.php" class="alert-link">create account  again</a>
            </div>
        <?php elseif ($message === 'error'): ?>
            <div class="alert alert-danger" role="alert">
                There was an error deleting your account. Please try again later.
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                Welcome to the confirmation page.
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-primary mt-3">Go to Home</a>
    </div>
</body>
</html>
