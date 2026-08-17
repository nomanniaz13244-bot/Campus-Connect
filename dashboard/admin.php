<?php
require_once '../includes/auth_check.php';
require_role(['admin']);
require_once '../config/db.php';

$page_title = "Admin Dashboard";

$user_count = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$complaint_count = $conn->query("SELECT COUNT(*) as c FROM complaints WHERE status = 'open'")->fetch_assoc()['c'];
$club_count = $conn->query("SELECT COUNT(*) as c FROM clubs")->fetch_assoc()['c'];
$pending_requests_count = $conn->query("SELECT COUNT(*) as c FROM club_members WHERE status = 'pending'")->fetch_assoc()['c'];

// Handle membership approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_action'])) {
    $membership_id = (int)($_POST['membership_id'] ?? 0);
    $action = $_POST['membership_action'] === 'approve' ? 'approved' : 'rejected';
    
    $upd = $conn->prepare("UPDATE club_members SET status = ? WHERE membership_id = ?");
    $upd->bind_param("si", $action, $membership_id);
    $upd->execute();
    
    header("Location: admin.php?tab=requests");
    exit;
}

include '../includes/header.php';
?>

<!-- MAIN CONTENT - SIDEBAR REMOVED (already in header.php) -->
<div class="page-container">
    <div class="dashboard-shell">
        <main style="width:100%;">
            <div class="card">
                <h2>Admin Control Panel</h2>
                <p style="color:var(--text-muted);">System-wide overview of Campus Connect.</p>
            </div>

            <div class="card-grid">
                <div class="stat-box"><div class="num"><?php echo $user_count; ?></div><div class="label">Total Users</div></div>
                <div class="stat-box"><div class="num"><?php echo $complaint_count; ?></div><div class="label">Open Complaints</div></div>
                <div class="stat-box"><div class="num"><?php echo $club_count; ?></div><div class="label">Active Clubs</div></div>
                <div class="stat-box"><div class="num"><?php echo $pending_requests_count; ?></div><div class="label">Pending Join Requests</div></div>
            </div>

            <div class="card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <h3 style="margin:0;">🎭 Club Management</h3>
                    <a href="../modules/clubs/add.php" class="btn btn-primary">+ Add Club</a>
                </div>
                
                <?php
                $clubs_list = $conn->query("
                    SELECT c.club_id, c.name, c.category, c.created_at, u.full_name as admin_name,
                           (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.club_id AND cm.status='approved') as member_count
                    FROM clubs c 
                    LEFT JOIN users u ON u.user_id = c.club_admin_id 
                    ORDER BY c.name ASC
                ");
                
                if ($clubs_list && $clubs_list->num_rows > 0):
                ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse;">
                            <thead style="border-bottom:2px solid var(--border);">
                                <tr>
                                    <th style="text-align:left; padding:10px;">Club Name</th>
                                    <th style="text-align:left; padding:10px;">Category</th>
                                    <th style="text-align:left; padding:10px;">Admin</th>
                                    <th style="text-align:center; padding:10px;">Members</th>
                                    <th style="text-align:left; padding:10px;">Created</th>
                                    <th style="text-align:center; padding:10px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($club = $clubs_list->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:10px;"><strong><?php echo htmlspecialchars($club['name']); ?></strong></td>
                                    <td style="padding:10px;"><?php echo htmlspecialchars($club['category']); ?></td>
                                    <td style="padding:10px;"><?php echo htmlspecialchars($club['admin_name'] ?: 'N/A'); ?></td>
                                    <td style="padding:10px; text-align:center;"><strong><?php echo (int)$club['member_count']; ?></strong></td>
                                    <td style="padding:10px; color:var(--text-muted); font-size:0.9rem;"><?php echo date('M d, Y', strtotime($club['created_at'])); ?></td>
                                    <td style="padding:10px; text-align:center;">
                                        <div style="display:flex; gap:4px; justify-content:center; flex-wrap:wrap;">
                                            <a href="../modules/clubs/view.php?id=<?php echo (int)$club['club_id']; ?>" class="btn btn-primary" style="padding:4px 8px; font-size:0.85rem;">View</a>
                                            <a href="../modules/clubs/edit.php?id=<?php echo (int)$club['club_id']; ?>" class="btn btn-outline" style="padding:4px 8px; font-size:0.85rem;">Edit</a>
                                            <a href="../modules/clubs/delete.php?id=<?php echo (int)$club['club_id']; ?>" class="btn btn-outline" style="padding:4px 8px; font-size:0.85rem; color:var(--danger); border-color:var(--danger);" onclick="return confirm('Delete this club?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="color:var(--text-muted);">No clubs found. <a href="../modules/clubs/add.php" style="color:var(--primary); font-weight:600;">Create one</a></p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Club Membership Requests</h3>
                <?php
                $pending = $conn->query("
                    SELECT cm.membership_id, cm.status, cm.joined_at, u.full_name as student_name, u.email, c.name as club_name 
                    FROM club_members cm 
                    JOIN users u ON cm.student_id = u.user_id 
                    JOIN clubs c ON cm.club_id = c.club_id 
                    WHERE cm.status = 'pending'
                    ORDER BY cm.joined_at DESC
                ");
                
                if ($pending->num_rows === 0):
                ?>
                    <p style="color:var(--text-muted);">No pending membership requests.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse;">
                            <thead style="border-bottom:2px solid var(--border);">
                                <tr>
                                    <th style="text-align:left; padding:10px;">Student Name</th>
                                    <th style="text-align:left; padding:10px;">Club Name</th>
                                    <th style="text-align:left; padding:10px;">Requested Date</th>
                                    <th style="text-align:center; padding:10px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $pending->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid var(--border);">
                                    <td style="padding:10px;">
                                        <strong><?php echo htmlspecialchars($row['student_name']); ?></strong>
                                        <div style="font-size:0.8rem; color:var(--text-muted);"><?php echo htmlspecialchars($row['email']); ?></div>
                                    </td>
                                    <td style="padding:10px;"><?php echo htmlspecialchars($row['club_name']); ?></td>
                                    <td style="padding:10px; color:var(--text-muted); font-size:0.9rem;"><?php echo date('M d, Y', strtotime($row['joined_at'])); ?></td>
                                    <td style="padding:10px; text-align:center;">
                                        <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="membership_id" value="<?php echo $row['membership_id']; ?>">
                                                <input type="hidden" name="membership_action" value="approve">
                                                <button type="submit" class="btn btn-primary" style="padding:6px 12px; font-size:0.85rem;">Approve</button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="membership_id" value="<?php echo $row['membership_id']; ?>">
                                                <input type="hidden" name="membership_action" value="reject">
                                                <button type="submit" class="btn btn-outline" style="padding:6px 12px; font-size:0.85rem; color:var(--danger); border-color:var(--danger);">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>