<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

// Get club ID from URL and validate
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("<div class='page-container'><div class='card' style='background:var(--danger); color:white;'><p>Invalid club ID.</p><a href='index.php' style='color:white; text-decoration:underline;'>Back to Clubs</a></div></div>");
}

// Fetch club details with prepared statement
$stmt = $conn->prepare("SELECT c.*, u.full_name AS admin_name FROM clubs c JOIN users u ON u.user_id = c.club_admin_id WHERE c.club_id = ?");
if (!$stmt) {
    die("<div class='page-container'><div class='card' style='background:var(--danger); color:white;'><p>Database error: " . htmlspecialchars($conn->error) . "</p></div></div>");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$club) {
    die("<div class='page-container'><div class='card'><p>Club not found. <a href='index.php' style='color:var(--primary);'>Back to Clubs</a></p></div></div>");
}

$page_title = $club['name'];

// Check current user's membership status
$membership = null;
if ($_SESSION['role'] === 'student') {
    $m = $conn->prepare("SELECT status FROM club_members WHERE club_id = ? AND student_id = ?");
    if ($m) {
        $m->bind_param("ii", $id, $_SESSION['user_id']);
        $m->execute();
        $membership = $m->get_result()->fetch_assoc();
        $m->close();
    }
}

// Handle join request
$join_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join']) && $_SESSION['role'] === 'student' && !$membership) {
    $ins = $conn->prepare("INSERT INTO club_members (club_id, student_id, status) VALUES (?, ?, 'pending')");
    if ($ins) {
        $ins->bind_param("ii", $id, $_SESSION['user_id']);
        if ($ins->execute()) {
            $join_message = "Request submitted! Waiting for admin approval.";
            // Refresh membership status
            $m = $conn->prepare("SELECT status FROM club_members WHERE club_id = ? AND student_id = ?");
            $m->bind_param("ii", $id, $_SESSION['user_id']);
            $m->execute();
            $membership = $m->get_result()->fetch_assoc();
            $m->close();
        } else {
            $join_message = "Error: " . $conn->error;
        }
        $ins->close();
    }
}

// Fetch club posts
$posts = $conn->prepare("SELECT post_id, title, content, created_at FROM club_posts WHERE club_id = ? ORDER BY created_at DESC");
if ($posts) {
    $posts->bind_param("i", $id);
    $posts->execute();
    $posts = $posts->get_result();
} else {
    $posts = null;
}

// Get member count
$member_count_result = $conn->prepare("SELECT COUNT(*) as c FROM club_members WHERE club_id = ? AND status='approved'");
if ($member_count_result) {
    $member_count_result->bind_param("i", $id);
    $member_count_result->execute();
    $member_count_row = $member_count_result->get_result()->fetch_assoc();
    $member_count_result->close();
} else {
    $member_count_row = ['c' => 0];
}

include '../../includes/header.php';
?>
<div class="page-container">
    <div class="card">
        <?php if ($club['logo_path']): ?>
            <img src="../../<?php echo htmlspecialchars($club['logo_path']); ?>" style="width:100%; max-height:220px; object-fit:cover; border-radius:8px; margin-bottom:14px;" alt="<?php echo htmlspecialchars($club['name']); ?>">
        <?php endif; ?>
        <h2 style="margin:0 0 6px;"><?php echo htmlspecialchars($club['name']); ?></h2>
        <p style="color:var(--text-muted); margin:0 0 10px;">
            <?php echo htmlspecialchars($club['category'] ?: 'General'); ?> · 
            <?php echo (int)$member_count_row['c']; ?> members · 
            Admin: <?php echo htmlspecialchars($club['admin_name']); ?>
        </p>
        <p><?php echo nl2br(htmlspecialchars($club['description'])); ?></p>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <div style="display:flex; gap:8px; margin-top:15px; padding-top:15px; border-top:1px solid var(--border);">
                <a href="edit.php?id=<?php echo (int)$id; ?>" class="btn btn-outline" style="flex:1; text-align:center;">Edit Club</a>
                <a href="delete.php?id=<?php echo (int)$id; ?>" class="btn btn-outline" style="flex:1; text-align:center; color:var(--danger); border-color:var(--danger);">Delete Club</a>
            </div>
        <?php endif; ?>

        <?php if ($join_message): ?>
            <div style="margin-top:15px; padding:12px; background:var(--success); color:white; border-radius:6px;">
                <?php echo htmlspecialchars($join_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'student'): ?>
            <?php if (!$membership): ?>
                <form method="POST" style="margin-top:15px;">
                    <button type="submit" name="join" class="btn btn-primary">Request to Join</button>
                </form>
            <?php elseif ($membership['status'] === 'pending'): ?>
                <div style="margin-top:15px; padding:12px; background:var(--primary); color:white; border-radius:6px; display:inline-block;">⏳ Your request is pending approval</div>
            <?php elseif ($membership['status'] === 'approved'): ?>
                <div style="margin-top:15px; padding:12px; background:var(--success); color:white; border-radius:6px; display:inline-block;">✅ You are a member of this club!</div>
            <?php else: ?>
                <div style="margin-top:15px; padding:12px; background:var(--danger); color:white; border-radius:6px; display:inline-block;">❌ Your request was rejected</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Club Updates</h3>
        <?php if (!$posts || $posts->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No updates posted yet.</p>
        <?php elseif ($posts): ?>
            <?php while ($p = $posts->fetch_assoc()): ?>
                <div style="padding:12px 0; border-bottom:1px solid var(--border);">
                    <h4 style="margin:0 0 4px;"><?php echo htmlspecialchars($p['title']); ?></h4>
                    <p style="margin:0 0 4px; color:var(--text-muted);"><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
                    <p style="margin:0; font-size:0.8rem; color:var(--text-muted);"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    <a href="index.php" style="color:var(--primary);">&larr; Back to Clubs</a>
</div>
<?php include '../../includes/footer.php'; ?>
