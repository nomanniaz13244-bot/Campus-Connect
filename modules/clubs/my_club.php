<?php
require_once '../../includes/auth_check.php';
require_role(['club_admin']);
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';
$page_title = "My Club";

$stmt = $conn->prepare("SELECT * FROM clubs WHERE club_admin_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$club = $stmt->get_result()->fetch_assoc();

$errors = [];

// CREATE CLUB (if club_admin has none yet)
if (!$club && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_club'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if ($name === '') $errors[] = "Club name is required.";

    $logo_path = null;
    if (empty($errors)) {
        try {
            $logo_path = handle_upload($_FILES['logo'] ?? null, 'clubs', ['jpg','jpeg','png','webp']);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        $ins = $conn->prepare("INSERT INTO clubs (club_admin_id, name, description, category, logo_path) VALUES (?, ?, ?, ?, ?)");
        $ins->bind_param("issss", $_SESSION['user_id'], $name, $description, $category, $logo_path);
        $ins->execute();
        header("Location: my_club.php");
        exit;
    }
}

// POST A CLUB UPDATE
if ($club && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_update'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title !== '') {
        $ins = $conn->prepare("INSERT INTO club_posts (club_id, title, content) VALUES (?, ?, ?)");
        $ins->bind_param("iss", $club['club_id'], $title, $content);
        $ins->execute();
        header("Location: my_club.php");
        exit;
    }
}

// APPROVE / REJECT MEMBERS
if ($club && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_action'])) {
    $membership_id = (int)$_POST['membership_id'];
    $action = $_POST['membership_action'] === 'approve' ? 'approved' : 'rejected';
    $upd = $conn->prepare("UPDATE club_members SET status = ? WHERE membership_id = ? AND club_id = ?");
    $upd->bind_param("sii", $action, $membership_id, $club['club_id']);
    $upd->execute();
    header("Location: my_club.php");
    exit;
}

include '../../includes/header.php';

if (!$club):
?>
<div class="page-container" style="max-width:600px;">
    <div class="card">
        <h2>Create Your Club</h2>
        <p style="color:var(--text-muted);">You haven't created a club yet. Set it up below.</p>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Club Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g. Tech, Sports, Arts">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label>Logo (optional)</label>
                <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
            </div>
            <button type="submit" name="create_club" class="btn btn-primary">Create Club</button>
        </form>
    </div>
</div>
<?php
else:
    $members = $conn->prepare("SELECT cm.membership_id, cm.club_id, cm.student_id, cm.status, cm.joined_at, u.full_name, u.email FROM club_members cm JOIN users u ON u.user_id = cm.student_id WHERE cm.club_id = ? ORDER BY cm.status, cm.joined_at DESC");
    if ($members) {
        $members->bind_param("i", $club['club_id']);
        $members->execute();
        $members = $members->get_result();
    } else {
        $members = null;
    }
?>
<div class="page-container">
    <div class="card">
        <h2><?php echo htmlspecialchars($club['name']); ?></h2>
        <p style="color:var(--text-muted);"><?php echo htmlspecialchars($club['category']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($club['description'])); ?></p>
        <a href="view.php?id=<?php echo $club['club_id']; ?>" class="btn btn-outline">View Public Page</a>
    </div>

    <div class="card">
        <h3>Post an Update</h3>
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" name="post_update" class="btn btn-primary">Post</button>
        </form>
    </div>

    <div class="card">
        <h3>Membership Requests & Members</h3>
        <?php if ($members->num_rows === 0): ?>
            <p style="color:var(--text-muted);">No membership activity yet.</p>
        <?php endif; ?>
        <?php while ($m = $members->fetch_assoc()): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border);">
                <div>
                    <strong><?php echo htmlspecialchars($m['full_name']); ?></strong>
                    <span style="color:var(--text-muted); font-size:0.85rem;"> · <?php echo htmlspecialchars($m['email']); ?></span>
                    <div><span class="badge-role" style="background:<?php echo $m['status']==='approved' ? 'var(--success)' : ($m['status']==='rejected' ? 'var(--danger)' : 'var(--primary)'); ?>;"><?php echo strtoupper($m['status']); ?></span></div>
                </div>
                <?php if ($m['status'] === 'pending'): ?>
                    <div style="display:flex; gap:8px;">
                        <form method="POST">
                            <input type="hidden" name="membership_id" value="<?php echo $m['membership_id']; ?>">
                            <input type="hidden" name="membership_action" value="approve">
                            <button type="submit" class="btn btn-primary">Approve</button>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="membership_id" value="<?php echo $m['membership_id']; ?>">
                            <input type="hidden" name="membership_action" value="reject">
                            <button type="submit" class="btn btn-outline" style="color:var(--danger); border-color:var(--danger);">Reject</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>
<?php include '../../includes/footer.php'; ?>
