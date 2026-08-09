<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';
$page_title = "Complaints";

// Admins get redirected to the management view
if ($_SESSION['role'] === 'admin') {
    header("Location: manage.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if ($subject === '') $errors[] = "Subject is required.";
    if ($description === '') $errors[] = "Description is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO complaints (student_id, subject, description, category) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $subject, $description, $category);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}

$my = $conn->prepare("SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC");
$my->bind_param("i", $_SESSION['user_id']);
$my->execute();
$my_complaints = $my->get_result();

include '../../includes/header.php';

function status_color($s) {
    return match($s) {
        'open' => 'var(--primary)',
        'in_progress' => 'var(--accent)',
        'resolved' => 'var(--success)',
        'rejected' => 'var(--danger)',
        default => 'var(--text-muted)'
    };
}
?>
<div class="page-container">
    <div class="card">
        <h2>📝 Submit a Complaint</h2>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <form method="POST">
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g. Facilities, Academics, Harassment">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Complaint</button>
        </form>
    </div>

    <div class="card">
        <h3>My Complaints</h3>
        <?php if ($my_complaints->num_rows === 0): ?>
            <p style="color:var(--text-muted);">You haven't submitted any complaints yet.</p>
        <?php endif; ?>
        <?php while ($c = $my_complaints->fetch_assoc()): ?>
            <div style="padding:14px 0; border-bottom:1px solid var(--border);">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0;"><?php echo htmlspecialchars($c['subject']); ?></h4>
                    <span class="badge-role" style="background:<?php echo status_color($c['status']); ?>;"><?php echo strtoupper(str_replace('_',' ',$c['status'])); ?></span>
                </div>
                <p style="color:var(--text-muted); font-size:0.9rem; margin:6px 0;"><?php echo nl2br(htmlspecialchars($c['description'])); ?></p>
                <?php if ($c['admin_response']): ?>
                    <div class="alert alert-success" style="margin-top:8px;"><strong>Admin response:</strong> <?php echo nl2br(htmlspecialchars($c['admin_response'])); ?></div>
                <?php endif; ?>
                <p style="font-size:0.8rem; color:var(--text-muted); margin:0;"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
