<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Clubs & Societies";

// Fetch all clubs with member counts using prepared statement
$query = "
    SELECT c.club_id, c.name, c.description, c.category, c.logo_path, c.created_at, u.full_name AS admin_name,
           (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.club_id AND cm.status='approved') AS member_count
    FROM clubs c 
    LEFT JOIN users u ON u.user_id = c.club_admin_id 
    ORDER BY c.name ASC
";

$clubs = $conn->query($query);

if (!$clubs) {
    die("<div class='page-container'><div class='card' style='background:var(--danger); color:white;'><p>Error fetching clubs: " . htmlspecialchars($conn->error) . "</p></div></div>");
}
?>
<?php include '../../includes/header.php'; ?>
<div class="page-container">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0;">🎭 Clubs & Societies</h2>
            <p style="color:var(--text-muted); margin:4px 0 0;">Discover and join campus clubs. <?php echo $clubs->num_rows; ?> club<?php echo $clubs->num_rows !== 1 ? 's' : ''; ?> available</p>
        </div>
        <div style="display:flex; gap:8px;">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="add.php" class="btn btn-primary">+ Add Club</a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] === 'club_admin'): ?>
                <a href="my_club.php" class="btn btn-primary">Manage My Club</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-grid">
        <?php if ($clubs->num_rows === 0): ?>
            <div style="grid-column: 1 / -1; padding:40px; text-align:center; color:var(--text-muted);">
                <p style="font-size:1.1rem;">No clubs available yet.</p>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <p><a href="add.php" class="btn btn-primary">Create the first club</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php while ($c = $clubs->fetch_assoc()): ?>
            <div class="card" style="display:flex; flex-direction:column;">
                <a href="view.php?id=<?php echo (int)$c['club_id']; ?>" style="text-decoration:none; flex:1;">
                    <?php if ($c['logo_path']): ?>
                        <img src="../../<?php echo htmlspecialchars($c['logo_path']); ?>" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:10px;" alt="<?php echo htmlspecialchars($c['name']); ?>">
                    <?php else: ?>
                        <div style="width:100%; height:120px; background:linear-gradient(135deg, var(--primary), var(--success)); border-radius:8px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:white; font-size:2.5rem;">🎭</div>
                    <?php endif; ?>
                    <h4 style="margin:0 0 6px; color:var(--text-dark);"><?php echo htmlspecialchars($c['name']); ?></h4>
                    <p style="color:var(--text-muted); font-size:0.85rem; margin:0 0 4px;">
                        <span style="background:var(--border); padding:2px 8px; border-radius:4px; display:inline-block;">
                            <?php echo htmlspecialchars($c['category'] ?: 'General'); ?>
                        </span>
                    </p>
                    <p style="font-size:0.85rem; color:var(--text-muted); margin:0 0 10px; display:flex; gap:4px;">
                        <span>👥 <?php echo (int)$c['member_count']; ?> member<?php echo $c['member_count'] !== '1' ? 's' : ''; ?></span>
                    </p>
                </a>
                
                <div style="display:flex; gap:6px; margin-top:auto;">
                    <a href="view.php?id=<?php echo (int)$c['club_id']; ?>" class="btn btn-primary" style="flex:1; text-align:center; padding:8px 4px; font-size:0.85rem;">View</a>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="edit.php?id=<?php echo (int)$c['club_id']; ?>" class="btn btn-outline" style="flex:1; text-align:center; padding:8px 4px; font-size:0.85rem;">Edit</a>
                        <a href="delete.php?id=<?php echo (int)$c['club_id']; ?>" class="btn btn-outline" style="flex:1; text-align:center; padding:8px 4px; font-size:0.85rem; color:var(--danger); border-color:var(--danger);">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
