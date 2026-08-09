<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Clubs & Societies";

$clubs = $conn->query("SELECT c.*, u.full_name AS admin_name,
                        (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.club_id AND cm.status='approved') AS member_count
                        FROM clubs c JOIN users u ON u.user_id = c.club_admin_id ORDER BY c.created_at DESC");
?>
<?php include '../../includes/header.php'; ?>
<div class="page-container">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0;">🎭 Clubs & Societies</h2>
            <p style="color:var(--text-muted); margin:4px 0 0;">Discover and join campus clubs</p>
        </div>
        <?php if ($_SESSION['role'] === 'club_admin'): ?>
            <a href="my_club.php" class="btn btn-primary">Manage My Club</a>
        <?php endif; ?>
    </div>

    <div class="card-grid">
        <?php if ($clubs->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No clubs yet.</p>
        <?php endif; ?>
        <?php while ($c = $clubs->fetch_assoc()): ?>
            <a href="view.php?id=<?php echo $c['club_id']; ?>" class="card" style="display:block;">
                <?php if ($c['logo_path']): ?>
                    <img src="../../<?php echo htmlspecialchars($c['logo_path']); ?>" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
                <?php endif; ?>
                <h4 style="margin:0 0 6px;"><?php echo htmlspecialchars($c['name']); ?></h4>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0 0 6px;"><?php echo htmlspecialchars($c['category'] ?: 'General'); ?></p>
                <p style="font-size:0.85rem; color:var(--text-muted); margin:0;"><?php echo (int)$c['member_count']; ?> members</p>
            </a>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
