<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT c.*, u.full_name AS admin_name FROM clubs c JOIN users u ON u.user_id = c.club_admin_id WHERE c.club_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

if (!$club) die("<p>Club not found. <a href='index.php'>Back</a></p>");

$page_title = $club['name'];

// Membership status for current user
$membership = null;
if ($_SESSION['role'] === 'student') {
    $m = $conn->prepare("SELECT status FROM club_members WHERE club_id = ? AND student_id = ?");
    $m->bind_param("ii", $id, $_SESSION['user_id']);
    $m->execute();
    $membership = $m->get_result()->fetch_assoc();
}

// Handle join request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join']) && $_SESSION['role'] === 'student' && !$membership) {
    $ins = $conn->prepare("INSERT INTO club_members (club_id, student_id, status) VALUES (?, ?, 'pending')");
    $ins->bind_param("ii", $id, $_SESSION['user_id']);
    $ins->execute();
    header("Location: view.php?id=$id");
    exit;
}

$posts = $conn->prepare("SELECT * FROM club_posts WHERE club_id = ? ORDER BY created_at DESC");
$posts->bind_param("i", $id);
$posts->execute();
$posts = $posts->get_result();

$member_count_row = $conn->query("SELECT COUNT(*) c FROM club_members WHERE club_id = $id AND status='approved'")->fetch_assoc();

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card">
        <?php if ($club['logo_path']): ?>
            <img src="../../<?php echo htmlspecialchars($club['logo_path']); ?>" style="width:100%; max-height:220px; object-fit:cover; border-radius:8px; margin-bottom:14px;">
        <?php endif; ?>
        <h2 style="margin:0 0 6px;"><?php echo htmlspecialchars($club['name']); ?></h2>
        <p style="color:var(--text-muted); margin:0 0 10px;"><?php echo htmlspecialchars($club['category'] ?: 'General'); ?> · <?php echo (int)$member_count_row['c']; ?> members · Admin: <?php echo htmlspecialchars($club['admin_name']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($club['description'])); ?></p>

        <?php if ($_SESSION['role'] === 'student'): ?>
            <?php if (!$membership): ?>
                <form method="POST">
                    <button type="submit" name="join" class="btn btn-primary">Request to Join</button>
                </form>
            <?php elseif ($membership['status'] === 'pending'): ?>
                <span class="alert alert-success" style="display:inline-block;">Join request pending approval</span>
            <?php elseif ($membership['status'] === 'approved'): ?>
                <span class="alert alert-success" style="display:inline-block;">✓ You're a member</span>
            <?php else: ?>
                <span class="alert alert-error" style="display:inline-block;">Join request was rejected</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Club Updates</h3>
        <?php if ($posts->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No updates posted yet.</p>
        <?php endif; ?>
        <?php while ($p = $posts->fetch_assoc()): ?>
            <div style="padding:12px 0; border-bottom:1px solid var(--border);">
                <h4 style="margin:0 0 4px;"><?php echo htmlspecialchars($p['title']); ?></h4>
                <p style="margin:0 0 4px; color:var(--text-muted);"><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
                <p style="margin:0; font-size:0.8rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
    <a href="index.php" style="color:var(--primary);">&larr; Back to Clubs</a>
</div>
<?php include '../../includes/footer.php'; ?>
