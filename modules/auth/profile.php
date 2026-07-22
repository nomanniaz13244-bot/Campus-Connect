<?php
include '../../includes/config/database.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch user data
$query = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Update profile
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email already exists for other user
    $check_query = "SELECT * FROM users WHERE email = '$email' AND user_id != '$user_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Email already taken by another user!";
    } else {
        $update_query = "UPDATE users SET name = '$name', email = '$email' WHERE user_id = '$user_id'";
        if(mysqli_query($conn, $update_query)) {
            $_SESSION['user_name'] = $name;
            $message = "Profile updated successfully!";
            // Refresh user data
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "Update failed! Please try again.";
        }
    }
}

// Change password
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required!";
    } elseif($new_password != $confirm_password) {
        $error = "New passwords do not match!";
    } elseif(strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Verify current password
        if(password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
            if(mysqli_query($conn, $update_query)) {
                $message = "Password changed successfully!";
            } else {
                $error = "Password change failed!";
            }
        } else {
            $error = "Current password is incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <h1 class="display-1">👤</h1>
                        <h5><?php echo $user['name']; ?></h5>
                        <p class="text-muted"><?php echo $user['email']; ?></p>
                        <span class="badge bg-primary"><?php echo $user['role']; ?></span>
                        <hr>
                        <p><small>Member since: <?php echo date('d M Y', strtotime($user['created_at'])); ?></small></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <?php if($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Update Profile Form -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">✏️ Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $user['email']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password Form -->
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">🔑 Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>