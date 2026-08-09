<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Lost & Found";

$type_filter = trim($_GET['type'] ?? '');

$sql = "SELECT l.*, u.full_name AS reporter_name FROM lost_found_items l JOIN users u ON u.user_id = l.reporter_id WHERE l.status = 'open'";
$params = []; $types = "";
if (in_array($type_filter, ['lost','found'], true)) {
    $sql .= " AND l.item_type = ?";
    $params[] = $type_filter; $types .= "s";
}
$sql .= " ORDER BY l.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$reports = $stmt->get_result();

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0;">🔍 Lost & Found</h2>
            <p style="color:var(--text-muted); margin:4px 0 0;">Report or browse lost and found items around campus</p>
        </div>
        <a href="report.php" class="btn btn-primary">+ Report an Item</a>
    </div>

    <div class="card">
        <div style="display:flex; gap:10px;">
            <a href="index.php" class="btn <?php echo $type_filter === '' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <a href="index.php?type=lost" class="btn <?php echo $type_filter === 'lost' ? 'btn-primary' : 'btn-outline'; ?>">Lost</a>
            <a href="index.php?type=found" class="btn <?php echo $type_filter === 'found' ? 'btn-primary' : 'btn-outline'; ?>">Found</a>
        </div>
    </div>

    <div class="card-grid">
        <?php if ($reports->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No reports found.</p>
        <?php endif; ?>
        <?php while ($r = $reports->fetch_assoc()): ?>
            <div class="card">
                <span class="badge-role" style="background:<?php echo $r['item_type'] === 'lost' ? 'var(--danger)' : 'var(--success)'; ?>;">
                    <?php echo strtoupper($r['item_type']); ?>
                </span>
                <?php if ($r['image_path']): ?>
                    <img src="../../<?php echo htmlspecialchars($r['image_path']); ?>" style="width:100%; height:140px; object-fit:cover; border-radius:8px; margin:10px 0;">
                <?php endif; ?>
                <h4 style="margin:8px 0 4px;"><?php echo htmlspecialchars($r['title']); ?></h4>
                <p style="color:var(--text-muted); font-size:0.9rem; margin:0 0 6px;"><?php echo htmlspecialchars($r['description']); ?></p>
                <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">📍 <?php echo htmlspecialchars($r['location'] ?: 'Not specified'); ?></p>
                <p style="font-size:0.8rem; color:var(--text-muted); margin:4px 0 10px;">Reported by <?php echo htmlspecialchars($r['reporter_name']); ?></p>
                <?php if ($r['reporter_id'] == $_SESSION['user_id']): ?>
                    <form method="POST" action="resolve.php">
                        <input type="hidden" name="id" value="<?php echo $r['report_id']; ?>">
                        <button type="submit" class="btn btn-outline" style="width:100%;">Mark Resolved</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
