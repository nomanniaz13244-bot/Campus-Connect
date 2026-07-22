<?php
include '../includes/config/database.php';

// Check if user is club admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'club_admin') {
    header("Location: ../modules/auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Admin - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <h2>🏛️ Club Admin Dashboard</h2>
        <p>Welcome, <strong><?php echo $_SESSION['user_name']; ?></strong>!</p>
        <hr>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4>Manage Your Club</h4>
                        <p>View and manage club activities</p>
                        <a href="#" class="btn btn-primary">Go to Club</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h4>Club Members</h4>
                        <p>View and manage members</p>
                        <a href="#" class="btn btn-success">Manage Members</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>📢 Announcements</h5>
                        <p>Post club announcements</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>📅 Events</h5>
                        <p>Manage club events</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h5>👥 Join Requests</h5>
                        <p>Approve new members</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>