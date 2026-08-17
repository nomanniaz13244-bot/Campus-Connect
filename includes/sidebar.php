<?php
// Sidebar is only shown for logged-in users (included from header.php)
if (!isset($_SESSION['user_id'])) {
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Initialize counts
$marketplace_count = 0;
$clubs_count = 0;
$notes_count = 0;
$complaints_count = 0;

// Get marketplace items count
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM marketplace_items WHERE seller_id = ? AND status = 'available'");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $marketplace_count = $row['c'] ?? 0;
    }
    $stmt->close();
}

// Get clubs joined count
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM club_members WHERE student_id = ? AND status = 'approved'");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $clubs_count = $row['c'] ?? 0;
    }
    $stmt->close();
}

// Get notes uploaded count
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM notes WHERE uploader_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $notes_count = $row['c'] ?? 0;
    }
    $stmt->close();
}

// Get complaints count
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM complaints WHERE student_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $complaints_count = $row['c'] ?? 0;
    }
    $stmt->close();
}

$current_role = $_SESSION['role'] ?? 'student';
$user_name = $_SESSION['full_name'] ?? 'User';
$user_initial = strtoupper(substr($user_name, 0, 1));
?>

<aside class="sidebar">
    <!-- User Profile -->
    <div class="sidebar-user">
        <div class="avatar"><?php echo $user_initial; ?></div>
        <h4><?php echo htmlspecialchars($user_name); ?></h4>
        <span class="role-badge"><?php echo ucfirst($current_role); ?></span>
    </div>
    
    <!-- Stats -->
    <div class="sidebar-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo (int)$marketplace_count; ?></span>
            <span class="stat-label">My Listings</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo (int)$clubs_count; ?></span>
            <span class="stat-label">Clubs Joined</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo (int)$notes_count; ?></span>
            <span class="stat-label">Notes Uploaded</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo (int)$complaints_count; ?></span>
            <span class="stat-label">Complaints</span>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav">
        <a href="/campus-connect/dashboard/<?php echo htmlspecialchars($current_role); ?>.php" class="nav-item">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        <a href="/campus-connect/modules/marketplace/index.php" class="nav-item">
            <i class="fas fa-store"></i>
            <span>Marketplace</span>
        </a>
        <a href="/campus-connect/modules/notes/index.php" class="nav-item">
            <i class="fas fa-book"></i>
            <span>Notes</span>
        </a>
        <a href="/campus-connect/modules/lostfound/index.php" class="nav-item">
            <i class="fas fa-search"></i>
            <span>Lost & Found</span>
        </a>
        <a href="/campus-connect/modules/clubs/index.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Clubs</span>
        </a>
        <a href="/campus-connect/modules/events/index.php" class="nav-item">
            <i class="fas fa-calendar"></i>
            <span>Events</span>
        </a>
        <a href="/campus-connect/modules/complaints/index.php" class="nav-item">
            <i class="fas fa-file-alt"></i>
            <span>Complaints</span>
        </a>
    </nav>
    
    <!-- Quick Actions -->
    <div class="sidebar-actions">
        <a href="/campus-connect/modules/marketplace/create.php" class="btn btn-primary btn-small">
            <i class="fas fa-plus"></i> Post Item
        </a>
        <a href="/campus-connect/modules/notes/upload.php" class="btn btn-primary btn-small">
            <i class="fas fa-upload"></i> Upload Notes
        </a>
        <a href="/campus-connect/modules/lostfound/report.php" class="btn btn-primary btn-small">
            <i class="fas fa-flag"></i> Report Lost/Found
        </a>
        <a href="/campus-connect/modules/complaints/index.php" class="btn btn-primary btn-small">
            <i class="fas fa-exclamation-circle"></i> Submit Complaint
        </a>
    </div>
    
    <!-- Logout -->
    <a href="/campus-connect/auth/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</aside>
