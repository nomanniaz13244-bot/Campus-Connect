<?php
require_once '../includes/auth_check.php';
require_role(['student']);
require_once '../config/db.php';

$page_title = "Student Dashboard";

$uid = (int)$_SESSION['user_id'];
$listing_count = $conn->query("SELECT COUNT(*) c FROM marketplace_items WHERE seller_id=$uid AND status='available'")->fetch_assoc()['c'];
$notes_count = $conn->query("SELECT COUNT(*) c FROM notes WHERE uploader_id=$uid")->fetch_assoc()['c'];
$clubs_count = $conn->query("SELECT COUNT(*) c FROM club_members WHERE student_id=$uid AND status='approved'")->fetch_assoc()['c'];
$complaints_count = $conn->query("SELECT COUNT(*) c FROM complaints WHERE student_id=$uid AND status IN ('open','in_progress')")->fetch_assoc()['c'];

include '../includes/header.php';
?>
<div class="page-container">
    <div class="dashboard-shell">
        <aside class="sidebar">
            <a href="student.php" class="active">🏠 Overview</a>
            <a href="../modules/marketplace/index.php">🛒 Marketplace</a>
            <a href="../modules/notes/index.php">📚 Notes</a>
            <a href="../modules/lostfound/index.php">🔍 Lost & Found</a>
            <a href="../modules/clubs/index.php">🎭 Clubs</a>
            <a href="../modules/events/index.php">📅 Events</a>
            <a href="../modules/complaints/index.php">📝 Complaints</a>
        </aside>
        <main>
            <div class="card">
                <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?> 👋</h2>
                <p style="color:var(--text-muted);">Here's what's happening around campus.</p>
            </div>

            <div class="card-grid" style="margin-bottom:20px;">
                <div class="stat-box"><div class="num"><?php echo (int)$listing_count; ?></div><div class="label">My Listings</div></div>
                <div class="stat-box"><div class="num"><?php echo (int)$notes_count; ?></div><div class="label">Notes Uploaded</div></div>
                <div class="stat-box"><div class="num"><?php echo (int)$clubs_count; ?></div><div class="label">Clubs Joined</div></div>
                <div class="stat-box"><div class="num"><?php echo (int)$complaints_count; ?></div><div class="label">Open Complaints</div></div>
            </div>

            <div class="card">
                <h3>Quick Actions</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="../modules/marketplace/create.php" class="btn btn-outline">Post Item</a>
                    <a href="../modules/notes/upload.php" class="btn btn-outline">Upload Notes</a>
                    <a href="../modules/lostfound/report.php" class="btn btn-outline">Report Lost/Found</a>
                    <a href="../modules/complaints/index.php" class="btn btn-outline">Submit Complaint</a>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
