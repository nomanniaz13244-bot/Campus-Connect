<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " | Campus Connect" : "Campus Connect"; ?></title>
    <link rel="stylesheet" href="/campus-connect/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="/campus-connect/index.php" class="brand">🎓 Campus Connect</a>
    <div class="nav-links">
        <a href="/campus-connect/modules/marketplace/index.php">Marketplace</a>
        <a href="/campus-connect/modules/notes/index.php">Notes</a>
        <a href="/campus-connect/modules/lostfound/index.php">Lost & Found</a>
        <a href="/campus-connect/modules/clubs/index.php">Clubs</a>
        <a href="/campus-connect/modules/events/index.php">Events</a>
        <a href="/campus-connect/modules/complaints/index.php">Complaints</a>
    </div>
    <?php if ($is_logged_in): ?>
        <div class="user-chip">
            <a href="/campus-connect/dashboard/<?php echo htmlspecialchars($role); ?>.php" class="btn btn-primary" style="padding:6px 16px; font-size:0.85rem;">Dashboard</a>
            <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <span class="badge-role"><?php echo htmlspecialchars($role); ?></span>
            <a href="/campus-connect/auth/logout.php" style="color:#dc2626;font-weight:600;">Logout</a>
        </div>
    <?php else: ?>
        <div class="user-chip">
            <a href="/campus-connect/auth/login.php">Login</a>
            &nbsp;|&nbsp;
            <a href="/campus-connect/auth/register.php">Register</a>
        </div>
    <?php endif; ?>
</nav>