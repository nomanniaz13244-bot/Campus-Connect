<?php
require_once '../../includes/auth_check.php';
require_role(['admin']);
require_once '../../config/db.php';
$page_title = "Manage Clubs";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_club'])) {
    $cid = (int)$_POST['club_id'];
    $conn->query("DELETE FROM clubs WHERE club_id = $cid");
    header("Location: manage.php");
    exit;
}

$clubs = $conn->query("SELECT c.*, u.full_name AS admin_name,
                        (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id=c.club_id AND cm.status='approved') AS member_count
                        FROM clubs c JOIN users u ON u.user_id = c.club_admin_id ORDER BY c.created_at DESC");
include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card">
        <h2>🎭 Manage Clubs (Admin)</h2>
    </div>
    <div class="card">
        <table style="width:100%; border-collapse:collapse;">
            <tr style="text-align:left; border-bottom:1px solid var(--border);">
                <th style="padding:8px;">Club</th><th>Admin</th><th>Members</th><th></th>
            </tr>
            <?php while ($c = $clubs->fetch_assoc()): ?>
            <tr style="border-bottom:1px solid var(--border);">
                <td style="padding:8px;"><?php echo htmlspecialchars($c['name']); ?></td>
                <td><?php echo htmlspecialchars($c['admin_name']); ?></td>
                <td><?php echo (int)$c['member_count']; ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Delete this club permanently?');">
                        <input type="hidden" name="club_id" value="<?php echo $c['club_id']; ?>">
                        <button type="submit" name="delete_club" class="btn btn-outline" style="color:var(--danger); border-color:var(--danger);">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
