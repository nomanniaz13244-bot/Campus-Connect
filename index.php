<?php
include 'includes/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="jumbotron bg-light p-5 rounded">
            <h1 class="display-4">🏫 Welcome to Campus Connect!</h1>
            <p class="lead">Your all-in-one university community platform.</p>
            <hr class="my-4">
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <p>Welcome back, <strong><?php echo $_SESSION['user_name']; ?></strong>!</p>
                <a href="modules/auth/logout.php" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <p>Please login or register to get started.</p>
                <a href="modules/auth/login.php" class="btn btn-primary">Login</a>
                <a href="modules/auth/register.php" class="btn btn-success">Register</a>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>