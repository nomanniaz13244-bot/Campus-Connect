<?php
require_once '../includes/auth_check.php';
require_role(['club_admin']);
require_once '../config/db.php';

$page_title = "Club Admin Dashboard";

$club = null;
$stmt = $conn->prepare("SELECT * FROM clubs WHERE club_admin_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

$member_count = 0; $pending_count = 0; $event_count = 0;
if ($club) {
    $member_count = $conn->query("SELECT COUNT(*) c FROM club_members WHERE club_id={$club['club_id']} AND status='approved'")->fetch_assoc()['c'];
    $pending_count = $conn->query("SELECT COUNT(*) c FROM club_members WHERE club_id={$club['club_id']} AND status='pending'")->fetch_assoc()['c'];
}
$event_count = $conn->query("SELECT COUNT(*) c FROM events WHERE posted_by=" . (int)$_SESSION['user_id'])->fetch_assoc()['c'];

include '../includes/header.php';
?>

<!-- MAIN CONTENT - SIDEBAR REMOVED (already in header.php) -->
<div class="page-container">
    <div class="dashboard-shell">
        <main style="width:100%;">
            <div class="card">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> 🎭</h2>
                <p style="color:var(--text-muted);">Manage your club, members and event postings from here.</p>
            </div>

            <div class="card-grid">
                <div class="stat-box"><div class="num"><?php echo (int)$member_count; ?></div><div class="label">Total Members</div></div>
                <div class="stat-box"><div class="num"><?php echo (int)$pending_count; ?></div><div class="label">Pending Requests</div></div>
                <div class="stat-box"><div class="num"><?php echo (int)$event_count; ?></div><div class="label">Events Posted</div></div>
            </div>
            
            <?php if ($club): ?>
            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h3 style="margin:0 0 4px;">Manage Members & Requests</h3>
                        <p style="margin:0; color:var(--text-muted); font-size:0.9rem;">Review and approve pending membership requests, view all club members.</p>
                    </div>
                    <a href="../modules/clubs/my_club.php" class="btn btn-primary">Go to Members</a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!$club): ?>
            <div class="card">
                <p style="color:var(--text-muted);">You haven't created a club yet. <a href="../modules/clubs/my_club.php" style="color:var(--primary); font-weight:600;">Create one now</a>.</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>