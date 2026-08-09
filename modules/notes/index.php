<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Academic Notes";

$search = trim($_GET['q'] ?? '');
$subject = trim($_GET['subject'] ?? '');
$semester = trim($_GET['semester'] ?? '');

$sql = "SELECT n.*, u.full_name AS uploader_name FROM notes n JOIN users u ON u.user_id = n.uploader_id WHERE 1=1";
$params = []; $types = "";

if ($search !== '') {
    $sql .= " AND (n.title LIKE ? OR n.description LIKE ?)";
    $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= "ss";
}
if ($subject !== '') { $sql .= " AND n.subject = ?"; $params[] = $subject; $types .= "s"; }
if ($semester !== '') { $sql .= " AND n.semester = ?"; $params[] = $semester; $types .= "s"; }
$sql .= " ORDER BY n.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$notes = $stmt->get_result();

$subjects = $conn->query("SELECT DISTINCT subject FROM notes ORDER BY subject");
$semesters = $conn->query("SELECT DISTINCT semester FROM notes ORDER BY semester");

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0;">📚 Academic Notes Sharing</h2>
            <p style="color:var(--text-muted); margin:4px 0 0;">Find and share study material by subject & semester</p>
        </div>
        <a href="upload.php" class="btn btn-primary">+ Upload Notes</a>
    </div>

    <div class="card">
        <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap;">
            <input type="text" name="q" class="form-control" style="flex:2; min-width:200px;" placeholder="Search notes..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="subject" class="form-control" style="flex:1; min-width:150px;">
                <option value="">All Subjects</option>
                <?php while ($s = $subjects->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($s['subject']); ?>" <?php echo $subject === $s['subject'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['subject']); ?></option>
                <?php endwhile; ?>
            </select>
            <select name="semester" class="form-control" style="flex:1; min-width:130px;">
                <option value="">All Semesters</option>
                <?php while ($s = $semesters->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($s['semester']); ?>" <?php echo $semester === $s['semester'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['semester']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-outline">Filter</button>
        </form>
    </div>

    <div class="card">
        <?php if ($notes->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No notes found.</p>
        <?php endif; ?>
        <?php while ($n = $notes->fetch_assoc()): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--border);">
                <div>
                    <h4 style="margin:0 0 4px;"><?php echo htmlspecialchars($n['title']); ?></h4>
                    <p style="margin:0; color:var(--text-muted); font-size:0.85rem;">
                        <?php echo htmlspecialchars($n['subject']); ?> · Semester <?php echo htmlspecialchars($n['semester']); ?> ·
                        by <?php echo htmlspecialchars($n['uploader_name']); ?> · <?php echo (int)$n['download_count']; ?> downloads
                    </p>
                </div>
                <a href="download.php?id=<?php echo $n['note_id']; ?>" class="btn btn-outline">Download</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
