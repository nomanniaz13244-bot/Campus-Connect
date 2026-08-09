<?php
require_once '../../includes/auth_check.php';
require_role(['admin']);
require_once '../../config/db.php';
$page_title = "Manage Complaints";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['complaint_id'];
    $status = $_POST['status'] ?? 'open';
    $response = trim($_POST['admin_response'] ?? '');

    if (in_array($status, ['open','in_progress','resolved','rejected'], true)) {
        $stmt = $conn->prepare("UPDATE complaints SET status = ?, admin_response = ? WHERE complaint_id = ?");
        $stmt->bind_param("ssi", $status, $response, $id);
        $stmt->execute();
    }
    header("Location: manage.php");
    exit;
}

$filter = trim($_GET['status'] ?? '');
$sql = "SELECT c.*, u.full_name AS student_name, u.email AS student_email FROM complaints c JOIN users u ON u.user_id = c.student_id";
if (in_array($filter, ['open','in_progress','resolved','rejected'], true)) {
    $sql .= " WHERE c.status = '" . $conn->real_escape_string($filter) . "'";
}
$sql .= " ORDER BY c.created_at DESC";
$complaints = $conn->query($sql);

function status_color($s) {
    return match($s) {
        'open' => 'var(--primary)',
        'in_progress' => 'var(--accent)',
        'resolved' => 'var(--success)',
        'rejected' => 'var(--danger)',
        default => 'var(--text-muted)'
    };
}

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card">
        <h2>📝 Manage Complaints</h2>
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
            <a href="manage.php" class="btn <?php echo $filter==='' ? 'btn-primary':'btn-outline'; ?>">All</a>
            <a href="manage.php?status=open" class="btn <?php echo $filter==='open' ? 'btn-primary':'btn-outline'; ?>">Open</a>
            <a href="manage.php?status=in_progress" class="btn <?php echo $filter==='in_progress' ? 'btn-primary':'btn-outline'; ?>">In Progress</a>
            <a href="manage.php?status=resolved" class="btn <?php echo $filter==='resolved' ? 'btn-primary':'btn-outline'; ?>">Resolved</a>
            <a href="manage.php?status=rejected" class="btn <?php echo $filter==='rejected' ? 'btn-primary':'btn-outline'; ?>">Rejected</a>
        </div>
    </div>

    <?php if ($complaints->num_rows === 0): ?>
        <div class="card"><p style="color:var(--text-muted);">No complaints found.</p></div>
    <?php endif; ?>

    <?php while ($c = $complaints->fetch_assoc()): ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="margin:0;"><?php echo htmlspecialchars($c['subject']); ?></h4>
                <span class="badge-role" style="background:<?php echo status_color($c['status']); ?>;"><?php echo strtoupper(str_replace('_',' ',$c['status'])); ?></span>
            </div>
            <p style="color:var(--text-muted); font-size:0.85rem; margin:6px 0;">
                From <?php echo htmlspecialchars($c['student_name']); ?> (<?php echo htmlspecialchars($c['student_email']); ?>)
                · <?php echo htmlspecialchars($c['category'] ?: 'General'); ?>
                · <?php echo date('M d, Y', strtotime($c['created_at'])); ?>
            </p>
            <p><?php echo nl2br(htmlspecialchars($c['description'])); ?></p>

            <form method="POST" style="margin-top:10px; border-top:1px solid var(--border); padding-top:12px;">
                <input type="hidden" name="complaint_id" value="<?php echo $c['complaint_id']; ?>">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="open" <?php echo $c['status']==='open'?'selected':''; ?>>Open</option>
                        <option value="in_progress" <?php echo $c['status']==='in_progress'?'selected':''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $c['status']==='resolved'?'selected':''; ?>>Resolved</option>
                        <option value="rejected" <?php echo $c['status']==='rejected'?'selected':''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Response</label>
                    <textarea name="admin_response" class="form-control" rows="2"><?php echo htmlspecialchars($c['admin_response'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>
<?php include '../../includes/footer.php'; ?>
