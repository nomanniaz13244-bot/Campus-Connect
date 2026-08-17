<?php
require_once '../../includes/auth_check.php';
require_login();
require_once '../../config/db.php';

// Only admin can delete clubs
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$club_id = (int)($_GET['id'] ?? 0);

if ($club_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch club data to verify it exists
$stmt = $conn->prepare("SELECT * FROM clubs WHERE club_id = ?");
if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $club_id);
$stmt->execute();
$result = $stmt->get_result();
$club = $result->fetch_assoc();
$stmt->close();

if (!$club) {
    header('Location: index.php');
    exit;
}

$page_title = "Delete Club: " . $club['name'];

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete club (foreign keys will handle cascading)
    $stmt = $conn->prepare("DELETE FROM clubs WHERE club_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("i", $club_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['success_message'] = "Club '{$club['name']}' has been deleted.";
            header('Location: index.php');
            exit;
        } else {
            $error = "Database error: " . $stmt->error;
            $stmt->close();
        }
    } else {
        $error = "Database error: " . $conn->error;
    }
}

include '../../includes/header.php';
?>

<div class="page-container" style="max-width:600px;">
    <div class="card" style="background:var(--danger); color:white; border-radius:6px;">
        <h2 style="margin:0 0 12px; color:white;">⚠️ Delete Club</h2>
        <p style="margin:0 0 20px;">You are about to permanently delete this club.</p>
        
        <div style="background:rgba(0,0,0,0.2); padding:15px; border-radius:6px; margin-bottom:20px;">
            <h3 style="margin:0 0 8px; color:white;">Club Name:</h3>
            <p style="margin:0; font-weight:bold; font-size:1.1rem;"><?php echo htmlspecialchars($club['name']); ?></p>
            
            <h3 style="margin:12px 0 8px; color:white;">Category:</h3>
            <p style="margin:0;"><?php echo htmlspecialchars($club['category']); ?></p>
        </div>
        
        <p style="margin:0 0 12px; line-height:1.6;">
            <strong>This action will:</strong>
            <ul style="margin:8px 0; padding-left:20px;">
                <li>Delete the club permanently</li>
                <li>Remove all club members and join requests</li>
                <li>Remove all club announcements and posts</li>
            </ul>
        </p>
        
        <p style="margin:0; color:#ffcccc;"><strong>This action cannot be undone!</strong></p>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="card" style="background:var(--warning); color:white; border-radius:6px; margin-bottom:20px;">
            <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div style="display:flex; gap:10px; margin-top:20px;">
        <form method="POST" style="flex:1;">
            <button type="submit" name="confirm_delete" class="btn btn-outline" style="width:100%; background:var(--danger); color:white; border:none; padding:12px;">
                Yes, Delete Club
            </button>
        </form>
        <a href="index.php" class="btn btn-primary" style="flex:1; text-align:center; padding:12px;">Cancel</a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
