<?php
require_once '../includes/auth_check.php';
require_role(['admin']);
require_once '../config/db.php';

$page_title = "Admin Dashboard";

$user_count = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$complaint_count = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE status = 'open'")->fetch_assoc()['c'];
$club_count = $conn->query("SELECT COUNT(*) as c FROM clubs")->fetch_assoc()['c'];

include '../includes/header.php';
?>
<div class="page-container">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <a href="admin.php" class="active">🏠 Overview</a>
            <a href="../modules/complaints/manage.php">📝 Complaints</a>
            <a href="../modules/clubs/manage.php">🎭 Clubs</a>
            <a href="#">👥 Users</a>
        </aside>
        <main>
            <div class="card">
                <h2>Admin Control Panel</h2>
                <p style="color:var(--text-muted);">System-wide overview of Campus Connect.</p>
            </div>

            <div class="card-grid">
                <div class="stat-box"><div class="num"><?php echo $user_count; ?></div><div class="label">Total Users</div></div>
                <div class="stat-box"><div class="num"><?php echo $complaint_count; ?></div><div class="label">Open Complaints</div></div>
                <div class="stat-box"><div class="num"><?php echo $club_count; ?></div><div class="label">Active Clubs</div></div>
            </div>
        </main>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
