<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Session security: Session timeout (30 minutes of inactivity)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: /campus-connect/auth/login.php');
    exit();
}
$_SESSION['last_activity'] = time();

$is_logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;

// Get pending requests count for admins
$pending_requests_count = 0;
if ($is_logged_in && $role === 'admin') {
    require_once __DIR__ . '/../config/db.php';
    $pending_result = $conn->query("SELECT COUNT(*) as c FROM club_members WHERE status = 'pending'");
    if ($pending_result) {
        $pending_requests_count = $pending_result->fetch_assoc()['c'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . " | Campus Connect" : "Campus Connect"; ?></title>
    <link rel="stylesheet" href="/campus-connect/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="app-container">
    <!-- Sidebar removed -->
    <div class="main-content">
        <nav class="navbar">
            <a href="/campus-connect/index.php" class="brand">🎓 Campus Connect</a>
            <div class="nav-links">
                <?php if ($is_logged_in): ?>
                    <a href="/campus-connect/dashboard/<?php echo htmlspecialchars($role); ?>.php" class="nav-dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
                <?php endif; ?>
                <a href="/campus-connect/index.php"><i class="fas fa-home"></i> Home</a>
                <a href="/campus-connect/modules/marketplace/index.php"><i class="fas fa-store"></i> Marketplace</a>
                <a href="/campus-connect/modules/notes/index.php"><i class="fas fa-book"></i> Notes</a>
                <a href="/campus-connect/modules/lostfound/index.php"><i class="fas fa-search"></i> Lost & Found</a>
                <a href="/campus-connect/modules/clubs/index.php"><i class="fas fa-users"></i> Clubs</a>
                <a href="/campus-connect/modules/events/index.php"><i class="fas fa-calendar"></i> Events</a>
                <a href="/campus-connect/modules/complaints/index.php"><i class="fas fa-file-alt"></i> Complaints</a>
                <?php if ($is_logged_in && $role === 'admin'): ?>
                    <a href="/campus-connect/modules/clubs/manage.php" style="border-left:1px solid var(--border); padding-left:16px; color:var(--primary); font-weight:600;">🎭 Manage Clubs</a>
                <?php endif; ?>
            </div>
            <?php if ($is_logged_in): ?>
                <div class="user-chip">
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