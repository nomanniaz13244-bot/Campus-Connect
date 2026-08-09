<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Events & Internships";

$category = trim($_GET['category'] ?? '');

$sql = "SELECT e.*, u.full_name AS poster_name FROM events e JOIN users u ON u.user_id = e.posted_by WHERE 1=1";
$params = []; $types = "";
if (in_array($category, ['event','workshop','internship'], true)) {
    $sql .= " AND e.category = ?"; $params[] = $category; $types .= "s";
}
$sql .= " ORDER BY e.event_date ASC, e.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result();

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0;">📅 Events & Internships</h2>
            <p style="color:var(--text-muted); margin:4px 0 0;">Workshops, campus events and internship opportunities</p>
        </div>
        <?php if (in_array($_SESSION['role'], ['club_admin','admin'], true)): ?>
            <a href="create.php" class="btn btn-primary">+ Post</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="index.php" class="btn <?php echo $category === '' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <a href="index.php?category=event" class="btn <?php echo $category === 'event' ? 'btn-primary' : 'btn-outline'; ?>">Events</a>
            <a href="index.php?category=workshop" class="btn <?php echo $category === 'workshop' ? 'btn-primary' : 'btn-outline'; ?>">Workshops</a>
            <a href="index.php?category=internship" class="btn <?php echo $category === 'internship' ? 'btn-primary' : 'btn-outline'; ?>">Internships</a>
        </div>
    </div>

    <div class="card-grid">
        <?php if ($events->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No listings found.</p>
        <?php endif; ?>
        <?php while ($e = $events->fetch_assoc()): ?>
            <div class="card">
                <span class="badge-role"><?php echo strtoupper($e['category']); ?></span>
                <?php if ($e['image_path']): ?>
                    <img src="../../<?php echo htmlspecialchars($e['image_path']); ?>" style="width:100%; height:130px; object-fit:cover; border-radius:8px; margin:10px 0;">
                <?php endif; ?>
                <h4 style="margin:8px 0 4px;"><?php echo htmlspecialchars($e['title']); ?></h4>
                <p style="color:var(--text-muted); font-size:0.9rem; margin:0 0 6px;"><?php echo htmlspecialchars($e['description']); ?></p>
                <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">
                    <?php if ($e['event_date']): ?>📆 <?php echo date('M d, Y', strtotime($e['event_date'])); ?> · <?php endif; ?>
                    📍 <?php echo htmlspecialchars($e['location'] ?: 'TBA'); ?>
                </p>
                <p style="font-size:0.8rem; color:var(--text-muted); margin-top:6px;">Posted by <?php echo htmlspecialchars($e['poster_name']); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
