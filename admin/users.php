<?php
include '../includes/config/database.php';

// Check if user is admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../modules/auth/login.php");
    exit();
}

// Delete user
if(isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $delete_query = "DELETE FROM users WHERE user_id = '$user_id'";
    mysqli_query($conn, $delete_query);
    header("Location: users.php");
    exit();
}

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <h2>👥 Manage Users</h2>
        <hr>
        
        <div class="card shadow">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><span class="badge bg-info"><?php echo $user['role']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if($user['role'] != 'admin'): ?>
                                        <a href="?delete=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure?')">
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Protected</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>