<?php
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in? Redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/" . $_SESSION['role'] . ".php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $errors[] = "Your account has been suspended. Contact admin.";
            } elseif (password_verify($password, $user['password_hash'])) {
                // Session regeneration for security
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time(); // Set last activity time

                header("Location: ../dashboard/" . $user['role'] . ".php");
                exit;
            } else {
                $errors[] = "Incorrect email or password.";
            }
        } else {
            $errors[] = "Incorrect email or password.";
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
    <title>Login | Campus Connect</title>
    <link rel="stylesheet" href="/campus-connect/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome back</h2>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Log In</button>
        </form>

        <p style="margin-top:16px; text-align:center; font-size:0.9rem;">
            Don't have an account? <a href="register.php" style="color:var(--primary); font-weight:600;">Register</a>
        </p>
    </div>
</div>
</body>
</html>
