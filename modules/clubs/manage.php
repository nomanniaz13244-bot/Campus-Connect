<?php
require_once '../../includes/auth_check.php';
require_role(['admin']);
require_once '../../config/db.php';
$page_title = "Manage Clubs";

$message = '';
$message_type = '';

// Handle club deletion with prepared statement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_club'])) {
    $cid = (int)($_POST['club_id'] ?? 0);
    
    if ($cid > 0) {
        // Use prepared statement for security
        $del = $conn->prepare("DELETE FROM clubs WHERE club_id = ?");
        $del->bind_param("i", $cid);
        
        if ($del->execute()) {
            $message = "Club deleted successfully.";
            $message_type = "success";
        } else {
            $message = "Error deleting club: " . $conn->error;
            $message_type = "error";
        }
    } else {
        $message = "Invalid club ID.";
        $message_type = "error";
    }
}

// Fetch all clubs with member counts using prepared statement
$clubs = $conn->query("
    SELECT c.club_id, c.name, c.description, c.category, c.logo_path, c.created_at, u.full_name AS admin_name,
           (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id=c.club_id AND cm.status='approved') AS member_count
    FROM clubs c 
    JOIN users u ON u.user_id = c.club_admin_id 
    ORDER BY c.created_at DESC
");

if (!$clubs) {
    die("Error fetching clubs: " . $conn->error);
}

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card">
        <h2>🎭 Manage Clubs (Admin)</h2>
        <p style="color:var(--text-muted);">View and manage all clubs on the platform.</p>
    </div>

    <?php if ($message): ?>
        <div class="card" style="background:<?php echo $message_type === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; color:white; border-radius:6px; padding:12px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="border-bottom:2px solid var(--border);">
                    <tr style="text-align:left;">
                        <th style="padding:12px; font-weight:bold;">Club Name</th>
                        <th style="padding:12px; font-weight:bold;">Admin</th>
                        <th style="padding:12px; font-weight:bold;">Category</th>
                        <th style="padding:12px; font-weight:bold;">Members</th>
                        <th style="padding:12px; font-weight:bold;">Created</th>
                        <th style="padding:12px; font-weight:bold;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($clubs->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" style="padding:20px; text-align:center; color:var(--text-muted);">
                            No clubs found on the platform.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php while ($c = $clubs->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:12px;">
                                <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                <div style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars(substr($c['description'] ?? '', 0, 50)); ?>...</div>
                            </td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($c['admin_name']); ?></td>
                            <td style="padding:12px;"><span class="badge-role"><?php echo htmlspecialchars($c['category'] ?: 'General'); ?></span></td>
                            <td style="padding:12px; text-align:center;"><strong><?php echo (int)$c['member_count']; ?></strong></td>
                            <td style="padding:12px; color:var(--text-muted); font-size:0.9rem;"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></td>
                            <td style="padding:12px;">
                                <div style="display:flex; gap:6px;">
                                    <a href="view.php?id=<?php echo $c['club_id']; ?>" class="btn btn-primary" style="padding:6px 10px; font-size:0.85rem;">View</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this club and all related data? This cannot be undone.');">
                                        <input type="hidden" name="club_id" value="<?php echo $c['club_id']; ?>">
                                        <button type="submit" name="delete_club" class="btn btn-outline" style="padding:6px 10px; font-size:0.85rem; color:var(--danger); border-color:var(--danger);">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
