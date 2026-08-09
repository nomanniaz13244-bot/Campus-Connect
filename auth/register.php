<?php
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $university_id = trim($_POST['university_id'] ?? '');
    $department = trim($_POST['department'] ?? '');

    // Validation
    if ($full_name === '' || strlen($full_name) < 3) {
        $errors[] = "Full name must be at least 3 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!in_array($role, ['student', 'club_admin'], true)) {
        // Only student/club_admin can self-register; 'admin' is seeded manually
        $errors[] = "Invalid role selected.";
    }

    if (empty($errors)) {
        // Check duplicate email
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role, university_id, department) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param("ssssss", $full_name, $email, $password_hash, $role, $university_id, $department);

            if ($insert->execute()) {
                $success = true;
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Campus Connect</title>
    <link rel="stylesheet" href="/campus-connect/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Create your account</h2>

        <?php if ($success): ?>
            <div class="alert alert-success">Registration successful! You can now <a href="login.php">log in</a>.</div>
        <?php else: ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>University ID</label>
                    <input type="text" name="university_id" class="form-control" value="<?php echo htmlspecialchars($_POST['university_id'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Register as</label>
                    <select name="role" class="form-control">
                        <option value="student">Student</option>
                        <option value="club_admin">Club Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
            </form>
        <?php endif; ?>

        <p style="margin-top:16px; text-align:center; font-size:0.9rem;">
            Already have an account? <a href="login.php" style="color:var(--primary); font-weight:600;">Log in</a>
        </p>
    </div>
</div>
</body>
</html>
