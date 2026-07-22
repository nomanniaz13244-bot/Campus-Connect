<?php
include '../includes/config/database.php';

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../modules/auth/login.php");
    exit();
}

// Get statistics
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$total_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM marketplace_items"))['count'];
$total_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints"))['count'];
$pending_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM complaints WHERE status='pending'"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <h2>📊 Admin Dashboard</h2>
        <p>Welcome, <strong><?php echo $_SESSION['user_name']; ?></strong>!</p>
        <hr>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h3><?php echo $total_items; ?></h3>
                        <p>Marketplace Items</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h3><?php echo $total_complaints; ?></h3>
                        <p>Total Complaints</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h3><?php echo $pending_complaints; ?></h3>
                        <p>Pending Complaints</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5>👥 Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
                        $users_result = mysqli_query($conn, $users_query);
                        ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($users_result)): ?>
                                    <tr>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><span class="badge bg-info"><?php echo $row['role']; ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5>📋 Recent Complaints</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $complaints_query = "SELECT * FROM complaints ORDER BY created_at DESC LIMIT 5";
                        $complaints_result = mysqli_query($conn, $complaints_query);
                        ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($complaints_result)): ?>
                                    <tr>
                                        <td><?php echo substr($row['subject'], 0, 20); ?>...</td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif($row['status'] == 'in_progress'): ?>
                                                <span class="badge bg-info">In Progress</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Resolved</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <a href="users.php" class="btn btn-primary w-100">Manage Users</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <a href="complaints.php" class="btn btn-warning w-100">Manage Complaints</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <a href="reports.php" class="btn btn-success w-100">View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>