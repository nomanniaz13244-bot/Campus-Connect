<?php
require_once '../../includes/auth_check.php';
require_role(['club_admin']);
require_once '../../config/db.php';
require_once '../../includes/upload_helper.php';
$page_title = "Create Club";

// Redirect if user already has a club
$existing = $conn->prepare("SELECT club_id FROM clubs WHERE club_admin_id = ?");
if ($existing) {
    $existing->bind_param("i", $_SESSION['user_id']);
    $existing->execute();
    if ($existing->get_result()->fetch_assoc()) {
        header("Location: my_club.php");
        exit;
    }
    $existing->close();
}

$errors = [];
$success = false;

// Handle club creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_club'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // Validation
    if ($name === '') {
        $errors[] = "Club name is required.";
    } elseif (strlen($name) < 3) {
        $errors[] = "Club name must be at least 3 characters.";
    }
    
    if (strlen($description) > 1000) {
        $errors[] = "Description must be less than 1000 characters.";
    }
    
    if (strlen($category) > 50) {
        $errors[] = "Category must be less than 50 characters.";
    }

    // Handle logo upload
    $logo_path = null;
    if (!empty($_FILES['logo']['name'])) {
        try {
            $logo_path = handle_upload($_FILES['logo'] ?? null, 'clubs', ['jpg','jpeg','png','webp']);
        } catch (Exception $e) {
            $errors[] = "Logo upload error: " . $e->getMessage();
        }
    }

    // Create club if no errors
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO clubs (club_admin_id, name, description, category, logo_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("issss", $_SESSION['user_id'], $name, $description, $category, $logo_path);
            if ($stmt->execute()) {
                $success = true;
                $club_id = $conn->insert_id;
            } else {
                $errors[] = "Error creating club: " . $conn->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}

include '../../includes/header.php';
?>
<div class="page-container" style="max-width:600px;">
    <?php if ($success): ?>
    <div class="card" style="background:var(--success); color:white; border-radius:6px; margin-bottom:20px;">
        <h3 style="margin:0 0 8px;">✅ Club Created Successfully!</h3>
        <p style="margin:0 0 12px;">Your club has been created and is now visible to students.</p>
        <a href="my_club.php" class="btn btn-primary" style="background:white; color:var(--success); border:none; font-weight:bold;">Go to Club Management</a>
    </div>
    <?php else: ?>
    <div class="card">
        <h2>Create New Club</h2>
        <p style="color:var(--text-muted);">As a club admin, create your club and start managing members.</p>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error" style="margin:12px 0; padding:10px; background:var(--danger); color:white; border-radius:6px;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endforeach; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name" style="font-weight:bold; display:block; margin-bottom:6px;">Club Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g., Computer Science Society" required maxlength="100" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">3-100 characters</small>
            </div>

            <div class="form-group">
                <label for="category" style="font-weight:bold; display:block; margin-bottom:6px;">Category</label>
                <input type="text" id="category" name="category" class="form-control" placeholder="e.g., Tech, Sports, Arts, Academic" maxlength="50" value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Optional - helps categorize your club</small>
            </div>

            <div class="form-group">
                <label for="description" style="font-weight:bold; display:block; margin-bottom:6px;">Description</label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Tell students about your club, its mission, activities, and what they'll gain by joining..." maxlength="1000"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <small style="color:var(--text-muted); display:block; margin-top:4px;">0-1000 characters</small>
            </div>

            <div class="form-group">
                <label for="logo" style="font-weight:bold; display:block; margin-bottom:6px;">Club Logo (Optional)</label>
                <input type="file" id="logo" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp" style="padding:8px;">
                <small style="color:var(--text-muted); display:block; margin-top:4px;">Accepted formats: JPG, JPEG, PNG, WebP (Max 5MB)</small>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="create_club" class="btn btn-primary" style="flex:1;">Create Club</button>
                <a href="index.php" class="btn btn-outline" style="flex:1; text-align:center; padding:10px;">Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php include '../../includes/footer.php'; ?>
